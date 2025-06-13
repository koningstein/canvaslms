<?php

namespace App\Services\Processing;

use App\Services\Analyzers\AverageCalculator;
use App\Services\Analyzers\StatisticsCalculator;
use App\Services\Analyzers\TrendAnalyzer;
use Illuminate\Support\Collection;

class AveragesReportProcessor
{
    public function __construct(
        protected AverageCalculator $averageCalculator,
        protected StatisticsCalculator $statisticsCalculator,
        protected TrendAnalyzer $trendAnalyzer
    ) {}

    public function processAveragesData(Collection $studentsProgress): array
    {
        $statistics = $this->statisticsCalculator->calculateBasicStats($studentsProgress);

        // GEBRUIK StatisticsCalculator voor assignment data (heeft alle view keys)
        $assignmentStats = $this->statisticsCalculator->calculateAssignmentStatistics($studentsProgress);

        // Gebruik AverageCalculator voor consistente student gemiddeldes
        $studentsWithAverages = $this->averageCalculator->calculateStudentAverages($studentsProgress, 'averages');

        // Bereken performance categorieën
        $performanceData = $this->calculatePerformanceCategories($studentsWithAverages);

        // Generate chart data - FIX HIER
        $chartData = $this->generateChartData($studentsWithAverages, $assignmentStats);

        // Calculate trend data
        $trendData = $this->trendAnalyzer->calculateTrendData($studentsProgress);

        // Generate insights
        $insights = $this->generateInsights($statistics, $assignmentStats, $performanceData);

        return [
            'statistics' => $statistics,
            'performanceData' => $performanceData,
            'assignmentAnalysis' => $assignmentStats, // Dit heeft alle keys die de view nodig heeft
            'chartData' => $chartData,
            'trendData' => $trendData,
            'insights' => $insights,
        ];
    }

    protected function calculatePerformanceCategories(Collection $studentsWithAverages): array
    {
        // Filter studenten met geldige gemiddeldes
        $validStudents = $studentsWithAverages->filter(function($student) {
            return $student['average_percentage'] !== null;
        })->sortByDesc('average_percentage');

        // Categoriseer studenten
        $topPerformers = $validStudents->filter(function($student) {
            return $student['average_percentage'] >= 75;
        })->take(10);

        $lowPerformers = $validStudents->filter(function($student) {
            return $student['average_percentage'] < 55;
        })->take(10);

        $studentsAbove75 = $validStudents->where('average_percentage', '>=', 75)->count();
        $studentsAbove55 = $validStudents->whereBetween('average_percentage', [55, 74.9])->count();
        $studentsBelow55 = $validStudents->where('average_percentage', '<', 55)->count();

        return [
            'topPerformers' => $topPerformers,
            'lowPerformers' => $lowPerformers,
            'studentsAbove75' => $studentsAbove75,
            'studentsAbove55' => $studentsAbove55,
            'studentsBelow55' => $studentsBelow55,
            'highestStudentAverage' => $topPerformers->first()['average_percentage'] ?? 0,
            'lowestStudentAverage' => $lowPerformers->first()['average_percentage'] ?? 0,
            'totalStudentsWithGrades' => $validStudents->count(),
        ];
    }

    protected function generateChartData(Collection $studentsWithAverages, Collection $assignmentStats): array
    {
        // Filter en sorteer studenten voor charts
        $sortedStudents = $studentsWithAverages->filter(function($student) {
            return $student['average_percentage'] !== null;
        })->sortByDesc('average_percentage')->take(15);

        // Student performance chart data
        $studentChartData = [
            'studentNames' => $sortedStudents->pluck('student_name')->toArray(),
            'studentPerformances' => $sortedStudents->pluck('average_percentage')->toArray(),
            'studentColors' => $sortedStudents->map(function($student) {
                return $this->getPerformanceColor($student['average_percentage']);
            })->toArray(),
        ];

        // Distribution chart data
        $distributionData = [
            'distributionLabels' => ['Goed (≥75%)', 'Voldoende (55-74%)', 'Onvoldoende (<55%)'],
            'distributionValues' => [
                $studentsWithAverages->where('average_percentage', '>=', 75)->count(),
                $studentsWithAverages->whereBetween('average_percentage', [55, 74.9])->count(),
                $studentsWithAverages->where('average_percentage', '<', 55)->count()
            ],
        ];

        // FIXED: Module/Assignment performance chart data
        // Filter opdrachten met geldige gemiddeldes en neem de top 12
        $validAssignments = $assignmentStats->filter(function($assignment) {
            return isset($assignment['average_percentage']) &&
                $assignment['average_percentage'] !== null &&
                $assignment['graded_count'] > 0;
        })->sortByDesc('average_percentage')->take(12);

        $moduleChartData = [
            'moduleNames' => $validAssignments->map(function($assignment) {
                $name = $assignment['assignment_name'];
                return strlen($name) > 20 ? substr($name, 0, 17) . '...' : $name;
            })->values()->toArray(),
            'modulePerformances' => $validAssignments->pluck('average_percentage')->values()->toArray(),
            'moduleColors' => $validAssignments->map(function($assignment) {
                return $this->getPerformanceColor($assignment['average_percentage'] ?? 0);
            })->values()->toArray(),
        ];

        // Debug logging
        \Log::info('Chart data generated', [
            'student_count' => count($studentChartData['studentNames']),
            'module_count' => count($moduleChartData['moduleNames']),
            'module_names' => $moduleChartData['moduleNames'],
            'module_performances' => $moduleChartData['modulePerformances']
        ]);

        return array_merge($studentChartData, $distributionData, $moduleChartData);
    }

    protected function generateInsights(array $statistics, Collection $assignmentStats, array $performanceData): array
    {
        $performanceInsights = [
            "Klasgemiddelde is {$statistics['average_percentage']}%",
            "Beste student behaalt {$performanceData['highestStudentAverage']}%",
            "Laagste student behaalt {$performanceData['lowestStudentAverage']}%",
            "{$performanceData['studentsAbove75']} studenten presteren goed (≥75%)",
        ];

        // Filter opdrachten voor insights
        $validAssignments = $assignmentStats->where('average_percentage', '!=', null);
        $lowPerformingAssignments = $assignmentStats->where('graded_count', '<', 3);

        $assignmentInsights = [
            "Gemiddeld {$statistics['completion_rate']}% van opdrachten beoordeeld",
            "Moeilijkste opdracht: " . ($validAssignments->sortBy('average_percentage')->first()['assignment_name'] ?? 'Onbekend'),
            "Makkelijkste opdracht: " . ($validAssignments->sortByDesc('average_percentage')->first()['assignment_name'] ?? 'Onbekend'),
            "{$lowPerformingAssignments->count()} opdrachten hebben weinig beoordelingen",
        ];

        return [
            'performance' => $performanceInsights,
            'assignments' => $assignmentInsights,
        ];
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
