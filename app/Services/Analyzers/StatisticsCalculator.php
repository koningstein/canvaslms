<?php

namespace App\Services\Analyzers;

use Illuminate\Support\Collection;

class StatisticsCalculator
{
    public function calculateBasicStats(Collection $studentsProgress): array
    {
        $totalStudents = $studentsProgress->count();
        $totalAssignments = $studentsProgress->isNotEmpty() ?
            $studentsProgress->first()['assignments']->count() : 0;

        return [
            'total_students' => $totalStudents,
            'total_assignments' => $totalAssignments,
            'completion_rate' => $this->calculateCompletionRate($studentsProgress, $totalStudents, $totalAssignments),
            'average_percentage' => $this->calculateOverallAverage($studentsProgress),
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

    public function calculateAssignmentStatistics(Collection $studentsProgress): array
    {
        if ($studentsProgress->isEmpty()) {
            return [];
        }

        $assignmentStats = [];
        $allAssignments = $studentsProgress->first()['assignments']->groupBy('module_name');

        foreach ($allAssignments as $moduleName => $assignments) {
            foreach ($assignments as $assignment) {
                $assignmentName = $assignment['assignment_name'];
                $pointsPossible = $assignment['points_possible'];

                $stats = $this->calculateSingleAssignmentStats($studentsProgress, $assignmentName, $pointsPossible);

                $assignmentStats[] = array_merge($stats, [
                    'assignment_name' => $assignmentName,
                    'module_name' => $moduleName,
                    'points_possible' => $pointsPossible,
                ]);
            }
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
}
