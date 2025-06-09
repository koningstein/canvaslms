<?php

namespace App\Services\Analyzers;

use Illuminate\Support\Collection;

class StatisticsCalculator
{
    public function calculateBasicStats($studentsProgress)
    {
        $totalStudents = $studentsProgress->count();
        $totalAssignments = $studentsProgress->isNotEmpty() ? $studentsProgress->first()['assignments']->count() : 0;

        // Bereken totaal behaalde punten en mogelijke punten
        $totalPointsAwarded = 0;
        $totalPointsPossible = 0;
        $totalGradedAssignments = 0;

        foreach ($studentsProgress as $student) {
            foreach ($student['assignments'] as $assignment) {
                if (isset($assignment['points_possible']) && $assignment['points_possible'] > 0) {
                    $totalPointsPossible += $assignment['points_possible'];

                    if (isset($assignment['score']) && $assignment['score'] !== null) {
                        $totalPointsAwarded += $assignment['score'];
                        $totalGradedAssignments++;
                    }
                }
            }
        }

        $averagePercentage = $totalPointsPossible > 0 ? round(($totalPointsAwarded / $totalPointsPossible) * 100, 1) : 0;
        $completionRate = ($totalStudents * $totalAssignments) > 0 ?
            round(($totalGradedAssignments / ($totalStudents * $totalAssignments)) * 100, 1) : 0;

        return [
            'total_students' => $totalStudents,
            'total_assignments' => $totalAssignments,
            'total_points_awarded' => $totalPointsAwarded,
            'total_points_possible' => $totalPointsPossible,
            'total_graded_assignments' => $totalGradedAssignments,
            'average_percentage' => $averagePercentage,
            'completion_rate' => $completionRate,
        ];
    }

    public function calculateGradeDistribution(Collection $studentsProgress): array
    {
        $distribution = [
            'excellent' => 0,  // ≥90%
            'good' => 0,       // 75-89%
            'sufficient' => 0, // 55-74%
            'insufficient' => 0, // <55%
            'not_graded' => 0,
        ];

        foreach ($studentsProgress as $student) {
            foreach ($student['assignments'] as $assignment) {
                if ($assignment['status'] === 'graded' &&
                    $assignment['points_possible'] > 0) {

                    $percentage = ($assignment['score'] / $assignment['points_possible']) * 100;

                    if ($percentage >= 90) {
                        $distribution['excellent']++;
                    } elseif ($percentage >= 75) {
                        $distribution['good']++;
                    } elseif ($percentage >= 55) {
                        $distribution['sufficient']++;
                    } else {
                        $distribution['insufficient']++;
                    }
                } else {
                    $distribution['not_graded']++;
                }
            }
        }

        return $distribution;
    }

    public function calculateAssignmentStatistics($studentsProgress)
    {
        $assignmentStats = [];

        if ($studentsProgress->isEmpty()) {
            return $assignmentStats;
        }

        // Verzamel alle unieke assignments
        $allAssignments = $studentsProgress->first()['assignments'];

        foreach ($allAssignments as $assignmentTemplate) {
            $assignmentName = $assignmentTemplate['assignment_name'];
            $moduleName = $assignmentTemplate['module_name'] ?? 'Onbekend';
            $pointsPossible = $assignmentTemplate['points_possible'] ?? 0;

            $scores = [];
            $gradedCount = 0;

            // Verzamel scores van alle studenten voor deze assignment
            foreach ($studentsProgress as $student) {
                $studentAssignment = $student['assignments']->where('assignment_name', $assignmentName)->first();
                if ($studentAssignment && isset($studentAssignment['score']) && $studentAssignment['score'] !== null) {
                    $scores[] = $studentAssignment['score'];
                    $gradedCount++;
                }
            }

            $averageScore = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;
            $averagePercentage = $pointsPossible > 0 ? round(($averageScore / $pointsPossible) * 100, 1) : 0;

            $assignmentStats[] = [
                'assignment_name' => $assignmentName,
                'module_name' => $moduleName,
                'points_possible' => $pointsPossible,
                'average_score' => $averageScore,
                'average_percentage' => $averagePercentage,
                'graded_count' => $gradedCount,
                'total_students' => $studentsProgress->count(),
                'completion_percentage' => $studentsProgress->count() > 0 ? round(($gradedCount / $studentsProgress->count()) * 100, 1) : 0,
            ];
        }

        return $assignmentStats;
    }

    protected function calculateCompletionRate(Collection $studentsProgress, int $totalStudents, int $totalAssignments): float
    {
        if ($totalStudents === 0 || $totalAssignments === 0) {
            return 0.0;
        }

        $completedCount = $studentsProgress->sum(function ($student) {
            return $student['assignments']->whereIn('status', ['graded', 'submitted'])->count();
        });

        $totalPossible = $totalStudents * $totalAssignments;
        return round(($completedCount / $totalPossible) * 100, 1);
    }

    protected function calculateOverallAverage(Collection $studentsProgress): float
    {
        $allPercentages = [];

        foreach ($studentsProgress as $student) {
            foreach ($student['assignments'] as $assignment) {
                if ($assignment['status'] === 'graded' &&
                    $assignment['points_possible'] > 0) {
                    $percentage = ($assignment['score'] / $assignment['points_possible']) * 100;
                    $allPercentages[] = $percentage;
                }
            }
        }

        return empty($allPercentages) ? 0.0 : round(array_sum($allPercentages) / count($allPercentages), 1);
    }

    protected function calculateSingleAssignmentStats(Collection $studentsProgress, string $assignmentName, float $pointsPossible): array
    {
        $gradedCount = 0;
        $percentageSum = 0;
        $scores = [];

        foreach ($studentsProgress as $student) {
            $studentAssignment = $student['assignments']->firstWhere('assignment_name', $assignmentName);

            if ($studentAssignment &&
                $studentAssignment['status'] === 'graded' &&
                $pointsPossible > 0) {

                $percentage = ($studentAssignment['score'] / $pointsPossible) * 100;
                $percentageSum += $percentage;
                $scores[] = $percentage;
                $gradedCount++;
            }
        }

        $averagePercentage = $gradedCount > 0 ? round($percentageSum / $gradedCount, 1) : 0;
        $completionPercentage = round(($gradedCount / $studentsProgress->count()) * 100, 1);

        return [
            'average_percentage' => $averagePercentage,
            'graded_count' => $gradedCount,
            'completion_percentage' => $completionPercentage,
            'difficulty_level' => $this->determineDifficultyLevel($averagePercentage, $gradedCount),
        ];
    }

    protected function determineDifficultyLevel(float $averagePercentage, int $gradedCount): string
    {
        if ($gradedCount < 3) {
            return 'Unknown';
        }

        return match (true) {
            $averagePercentage >= 80 => 'Easy',
            $averagePercentage >= 65 => 'Medium',
            $averagePercentage >= 50 => 'Hard',
            default => 'Very Hard'
        };
    }

    public function calculateMissingStats($studentsProgress)
    {
        // Bereken verschillende tellingen
        $totalMissing = $studentsProgress->sum(fn($s) => $s['assignments']->where('display_value', 'Ontbreekt')->count());
        $totalInsufficient = $studentsProgress->sum(fn($s) => $s['assignments']->where('display_value', 'Onvoldoende')->count());
        $totalLate = $studentsProgress->sum(fn($s) => $s['assignments']->where('display_value', 'Te laat')->count());
        $totalProblematic = $totalMissing + $totalInsufficient + $totalLate;

        $totalStudents = $studentsProgress->count();

        // Filter studenten met problemen
        $studentsWithProblemsCount = $studentsProgress->filter(function($student) {
            return $student['assignments']->whereIn('display_value', ['Ontbreekt', 'Onvoldoende', 'Te laat'])->count() > 0;
        })->count();

        $problemRate = $totalStudents > 0 ? round(($studentsWithProblemsCount / $totalStudents) * 100, 1) : 0;

        return [
            'totalMissing' => $totalMissing,
            'totalInsufficient' => $totalInsufficient,
            'totalLate' => $totalLate,
            'totalProblematic' => $totalProblematic,
            'totalStudents' => $totalStudents,
            'studentsWithProblemsCount' => $studentsWithProblemsCount,
            'problemRate' => $problemRate,
        ];
    }
}
