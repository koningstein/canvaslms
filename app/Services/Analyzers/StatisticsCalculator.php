<?php

namespace App\Services\Analyzers;

use Illuminate\Support\Collection;

class StatisticsCalculator
{
    public function calculateBasicStats(Collection $studentsProgress): array
    {
        $totalStudents = $studentsProgress->count();
        $totalAssignments = $studentsProgress->isNotEmpty() ? $studentsProgress->first()['assignments']->count() : 0;

        // Tel submissions (graded + submitted)
        $totalSubmissions = $studentsProgress->sum(function($student) {
            return $student['assignments']->whereIn('status', ['graded', 'submitted'])->count();
        });

        // Tel alleen graded assignments
        $totalGradedAssignments = $studentsProgress->sum(function($student) {
            return $student['assignments']->where('status', 'graded')->count();
        });

        // Bereken totaal punten voor grades rapport
        $totalPointsAwarded = $studentsProgress->sum(function($student) {
            return $student['assignments']->filter(function($assignment) {
                return isset($assignment['score']) && is_numeric($assignment['score']);
            })->sum('score');
        });

        $totalPointsPossible = $studentsProgress->sum(function($student) {
            return $student['assignments']->filter(function($assignment) {
                return isset($assignment['points_possible']) && is_numeric($assignment['points_possible']);
            })->sum('points_possible');
        });

        // Bereken completion rate
        $totalPossible = $totalStudents * $totalAssignments;
        $completionRate = $totalPossible > 0 ? round(($totalSubmissions / $totalPossible) * 100, 1) : 0;

        // Bereken gemiddeld percentage van alle beoordeelde opdrachten
        $allGradedPercentages = $studentsProgress->flatMap(function($student) {
            return $student['assignments']->filter(function($assignment) {
                return isset($assignment['score']) &&
                    is_numeric($assignment['score']) &&
                    isset($assignment['points_possible']) &&
                    $assignment['points_possible'] > 0;
            })->map(function($assignment) {
                return ($assignment['score'] / $assignment['points_possible']) * 100;
            });
        });

        $averagePercentage = $allGradedPercentages->isNotEmpty() ?
            round($allGradedPercentages->avg(), 1) : 0;

        // Return ALLE data in beide naming conventions voor compatibility
        return [
            // Snake case (nieuw/preferred)
            'total_students' => $totalStudents,
            'total_assignments' => $totalAssignments,
            'total_submissions' => $totalSubmissions,
            'total_graded_assignments' => $totalGradedAssignments,
            'total_points_awarded' => $totalPointsAwarded,
            'total_points_possible' => $totalPointsPossible,
            'completion_rate' => $completionRate,
            'average_percentage' => $averagePercentage,

            // Camel case (voor bestaande views)
            'totalStudents' => $totalStudents,
            'totalAssignments' => $totalAssignments,
            'totalSubmissions' => $totalSubmissions,
            'totalGradedAssignments' => $totalGradedAssignments,
            'totalPointsAwarded' => $totalPointsAwarded,
            'totalPointsPossible' => $totalPointsPossible,
            'completionRate' => $completionRate,
            'averagePercentage' => $averagePercentage,
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

    public function calculateAssignmentStatistics(Collection $studentsProgress): Collection
    {
        if ($studentsProgress->isEmpty()) {
            return collect();
        }

        $allAssignments = $studentsProgress->first()['assignments'];

        return $allAssignments->map(function ($assignment) use ($studentsProgress) {
            $assignmentName = $assignment['assignment_name'];

            // Verzamel alle student responses voor deze opdracht
            $responses = $studentsProgress->map(function ($student) use ($assignmentName) {
                return $student['assignments']->where('assignment_name', $assignmentName)->first();
            })->filter();

            $totalResponses = $responses->count();
            $gradedResponses = $responses->where('status', 'graded');
            $submittedResponses = $responses->whereIn('status', ['graded', 'submitted']);

            // Bereken gemiddelde als er scores zijn
            $averageScore = null;
            $averagePercentage = null;

            if ($gradedResponses->isNotEmpty()) {
                $validScores = $gradedResponses->filter(function ($response) {
                    return isset($response['score']) &&
                        is_numeric($response['score']) &&
                        isset($response['points_possible']) &&
                        $response['points_possible'] > 0;
                });

                if ($validScores->isNotEmpty()) {
                    $averageScore = round($validScores->avg('score'), 1);
                    $totalScore = $validScores->sum('score');
                    $totalPossible = $validScores->sum('points_possible');
                    $averagePercentage = $totalPossible > 0 ? round(($totalScore / $totalPossible) * 100, 1) : 0;
                }
            }

            return array_merge($assignment, [
                'total_responses' => $totalResponses,
                'graded_count' => $gradedResponses->count(),
                'submitted_count' => $submittedResponses->count(),
                'average_score' => $averageScore,
                'average_percentage' => $averagePercentage,
                'completion_rate' => $totalResponses > 0 ? round(($submittedResponses->count() / $totalResponses) * 100, 1) : 0,
            ]);
        });
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

    public function calculateMissingStats(Collection $studentsProgress): array
    {
        $totalStudents = $studentsProgress->count();

        $totalMissing = $studentsProgress->sum(function($student) {
            return $student['assignments']->where('status', 'missing')->count();
        });

        $totalInsufficient = $studentsProgress->sum(function($student) {
            return $student['assignments']->filter(function($assignment) {
                return isset($assignment['score']) &&
                    isset($assignment['points_possible']) &&
                    $assignment['points_possible'] > 0 &&
                    (($assignment['score'] / $assignment['points_possible']) * 100) < 55;
            })->count();
        });

        $totalProblematic = $totalMissing + $totalInsufficient;

        $studentsWithProblemsCount = $studentsProgress->filter(function($student) {
            $missing = $student['assignments']->where('status', 'missing')->count();
            $insufficient = $student['assignments']->filter(function($assignment) {
                return isset($assignment['score']) &&
                    isset($assignment['points_possible']) &&
                    $assignment['points_possible'] > 0 &&
                    (($assignment['score'] / $assignment['points_possible']) * 100) < 55;
            })->count();

            return ($missing + $insufficient) > 0;
        })->count();

        $problemRate = $totalStudents > 0 ? round(($studentsWithProblemsCount / $totalStudents) * 100, 1) : 0;

        return [
            'totalMissing' => $totalMissing,
            'totalInsufficient' => $totalInsufficient,
            'totalProblematic' => $totalProblematic,
            'studentsWithProblemsCount' => $studentsWithProblemsCount,
            'problemRate' => $problemRate,
            'totalStudents' => $totalStudents,
        ];
    }
}
