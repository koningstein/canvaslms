<?php

namespace App\Services\Analyzers;

use Illuminate\Support\Collection;

class ChartDataGenerator
{
    public function generateStudentPerformanceChart(Collection $studentsProgress, int $maxStudents = 15): array
    {
        $studentsWithGrades = $studentsProgress->filter(function ($student) {
            return $student['assignments']->where('status', 'graded')->count() > 0;
        })->take($maxStudents);

        $names = [];
        $performances = [];
        $colors = [];

        foreach ($studentsWithGrades as $student) {
            $averagePercentage = $this->calculateStudentAverage($student);

            $names[] = $student['student_name'];
            $performances[] = $averagePercentage;
            $colors[] = $this->getPerformanceColor($averagePercentage);
        }

        return [
            'names' => $names,
            'performances' => $performances,
            'colors' => $colors,
        ];
    }

    public function generatePerformanceDistributionChart(Collection $studentsProgress): array
    {
        $distribution = $this->calculatePerformanceDistribution($studentsProgress);

        return [
            'labels' => ['Goed (≥75%)', 'Voldoende (55-74%)', 'Onvoldoende (<55%)'],
            'values' => [$distribution['good'], $distribution['sufficient'], $distribution['insufficient']],
            'colors' => ['#10B981', '#F59E0B', '#EF4444'],
        ];
    }

    public function generateModulePerformanceChart(array $assignmentStats, int $maxAssignments = 12): array
    {
        $filteredStats = array_filter($assignmentStats, fn($stat) => $stat['graded_count'] > 0);
        $limitedStats = array_slice($filteredStats, 0, $maxAssignments);

        $names = [];
        $performances = [];
        $colors = [];

        foreach ($limitedStats as $stat) {
            $shortName = strlen($stat['assignment_name']) > 20 ?
                substr($stat['assignment_name'], 0, 17) . '...' :
                $stat['assignment_name'];

            $names[] = $shortName;
            $performances[] = $stat['average_percentage'];
            $colors[] = $this->getPerformanceColor($stat['average_percentage']);
        }

        return [
            'names' => $names,
            'performances' => $performances,
            'colors' => $colors,
        ];
    }

    public function generateTrendChart(array $trendData): array
    {
        return [
            'dates' => $trendData['dates'] ?? [],
            'values' => $trendData['values'] ?? [],
            'color' => '#8B5CF6',
        ];
    }

    protected function calculateStudentAverage(array $student): float
    {
        $gradedAssignments = $student['assignments']->where('status', 'graded');
        $totalPercentage = 0;
        $count = 0;

        foreach ($gradedAssignments as $assignment) {
            if ($assignment['points_possible'] > 0) {
                $percentage = ($assignment['score'] / $assignment['points_possible']) * 100;
                $totalPercentage += $percentage;
                $count++;
            }
        }

        return $count > 0 ? round($totalPercentage / $count, 1) : 0;
    }

    protected function calculatePerformanceDistribution(Collection $studentsProgress): array
    {
        $distribution = ['good' => 0, 'sufficient' => 0, 'insufficient' => 0];

        foreach ($studentsProgress as $student) {
            $average = $this->calculateStudentAverage($student);

            if ($average >= 75) {
                $distribution['good']++;
            } elseif ($average >= 55) {
                $distribution['sufficient']++;
            } else {
                $distribution['insufficient']++;
            }
        }

        return $distribution;
    }

    protected function getPerformanceColor(float $percentage): string
    {
        return match (true) {
            $percentage >= 75 => '#10B981', // Green
            $percentage >= 55 => '#F59E0B', // Yellow
            default => '#EF4444'            // Red
        };
    }
}
