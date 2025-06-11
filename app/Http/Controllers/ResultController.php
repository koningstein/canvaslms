<?php

namespace App\Http\Controllers;

use App\Configuration\ReportConfigurationFactory;
use App\Services\Processing\ConfigurableReportProcessor;
use App\Services\Processing\MissingReportProcessor;
use App\Services\Processing\GradesReportProcessor;
use App\Services\Processing\PercentagesReportProcessor;
use App\Services\Analyzers\AverageCalculator;
use App\Services\Analyzers\PerformanceAnalyzer;
use App\Services\Analyzers\TrendAnalyzer;
use App\Services\Analyzers\StatisticsCalculator;
use App\Services\Analyzers\ChartDataGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResultController extends Controller
{
    public function __construct(
        protected ConfigurableReportProcessor $reportProcessor,
        protected MissingReportProcessor $missingReportProcessor,
        protected GradesReportProcessor $gradesReportProcessor,
        protected PercentagesReportProcessor $percentagesReportProcessor,
        protected AverageCalculator $averageCalculator,
        protected PerformanceAnalyzer $performanceAnalyzer,
        protected TrendAnalyzer $trendAnalyzer,
        protected StatisticsCalculator $statisticsCalculator,
        protected ChartDataGenerator $chartDataGenerator
    ) {}

    public function getSelectedProgress()
    {
        try {
            $students = session('selected_users', []);
            $courseModules = session('selected_modules', []);
            $reportType = session('report_type', 'basic');

            // Log voor debugging
            Log::info('Report Controller called', [
                'report_type' => $reportType,
                'students_count' => count($students),
                'modules_count' => count($courseModules)
            ]);

            $config = ReportConfigurationFactory::create($reportType);
            $studentsProgress = $this->reportProcessor->processReport($students, $courseModules, $config);

            return $this->renderReport($studentsProgress, $config);

        } catch (\Exception $e) {
            Log::error('Report Controller Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function renderReport($studentsProgress, $config)
    {
        $viewData = ['studentsProgress' => $studentsProgress, 'reportType' => $config->reportType];

        switch ($config->reportType) {
            case 'basic':
                return $this->renderBasicReport($studentsProgress, $viewData);
            case 'grades':
                return $this->renderGradesReport($studentsProgress, $viewData);
            case 'percentages':
                return $this->renderPercentagesReport($studentsProgress, $viewData);
            case 'missing':
                return $this->renderMissingReport($studentsProgress, $viewData);
            case 'attention':
                return $this->renderAttentionReport($studentsProgress, $viewData);
            case 'averages':
                return $this->renderAveragesReport($studentsProgress, $viewData);
            default:
                return $this->renderBasicReport($studentsProgress, $viewData);
        }
    }

    protected function renderBasicReport($studentsProgress, $viewData)
    {
        // Gebruik StatisticsCalculator voor basis statistieken
        $statistics = $this->statisticsCalculator->calculateBasicStats($studentsProgress);

        // Bereken gemiddeldes
        $studentsWithAverages = $this->averageCalculator->calculateStudentAverages($studentsProgress);
        $assignmentAverages = $this->averageCalculator->calculateAssignmentAverages($studentsProgress);
        $classAverageData = $this->averageCalculator->calculateClassAverage($studentsProgress);

        return view('results.basic-color-report', array_merge($viewData, [
            'totalStudents' => $statistics['total_students'],
            'totalAssignments' => $statistics['total_assignments'],
            'totalSubmissions' => $statistics['total_submissions'],
            'completionRate' => $statistics['completion_rate'],
            'studentsWithAverages' => $studentsWithAverages,
            'assignmentAverages' => $assignmentAverages,
            'classAverageData' => $classAverageData,
        ]));
    }

    protected function renderGradesReport($studentsProgress, $viewData)
    {
        // Gebruik de dedicated GradesReportProcessor
        $gradesData = $this->gradesReportProcessor->processGradesData($studentsProgress);
        $statistics = $this->statisticsCalculator->calculateBasicStats($studentsProgress);

        // Voeg gemiddeldes toe voor herbruikbaarheid
        $assignmentAverages = $this->averageCalculator->calculateAssignmentAverages($studentsProgress);
        $classAverageData = $this->averageCalculator->calculateClassAverage($studentsProgress);

        return view('results.grades-report', array_merge($viewData, [
            'totalStudents' => $statistics['total_students'],
            'totalAssignments' => $statistics['total_assignments'],
            'totalPointsAwarded' => $gradesData['totalPointsAwarded'],
            'totalPointsPossible' => $gradesData['totalPointsPossible'],
            'averagePercentage' => $gradesData['averagePercentage'],
            'assignmentGroups' => $gradesData['assignmentGroups'],
            'studentsWithScores' => $gradesData['studentsWithScores'],
            'assignmentAverages' => $assignmentAverages,
            'classAverageData' => $classAverageData,
        ]));
    }

    protected function renderPercentagesReport($studentsProgress, $viewData)
    {
        // Hergebruik GradesReportProcessor + Statistics
        $percentagesData = $this->percentagesReportProcessor->processPercentagesData($studentsProgress);
        $statistics = $this->statisticsCalculator->calculateBasicStats($studentsProgress);

        // Voeg gemiddeldes toe voor herbruikbaarheid
        $assignmentAverages = $this->averageCalculator->calculateAssignmentAverages($studentsProgress);
        $classAverageData = $this->averageCalculator->calculateClassAverage($studentsProgress);

        return view('results.percentages-report', array_merge($viewData, [
            'totalStudents' => $statistics['total_students'],
            'totalAssignments' => $statistics['total_assignments'],
            'totalGradedAssignments' => $statistics['total_graded_assignments'],
            'averagePercentage' => $statistics['average_percentage'],
            'completionRate' => $statistics['completion_rate'],
            'assignmentGroups' => $percentagesData['assignmentGroups'],
            'studentsWithPercentages' => $percentagesData['studentsWithPercentages'],
            'assignmentAverages' => $assignmentAverages,
            'classAverageData' => $classAverageData,
        ]));
    }

    protected function renderMissingReport($studentsProgress, $viewData)
    {
        // Gebruik de dedicated services
        $missingStats = $this->statisticsCalculator->calculateMissingStats($studentsProgress);
        $processedData = $this->missingReportProcessor->processMissingData($studentsProgress);

        return view('results.missing-report', array_merge($viewData, $missingStats, $processedData));
    }

    protected function renderAttentionReport($studentsProgress, $viewData)
    {
        $studentsWithRisk = $this->performanceAnalyzer->calculateAttentionRisks($studentsProgress);

        return view('results.attention-report', array_merge($viewData, [
            'studentsWithRisk' => $studentsWithRisk,
            'urgentCount' => $studentsWithRisk->where('risk_level', 'urgent')->count(),
            'highCount' => $studentsWithRisk->where('risk_level', 'high')->count(),
            'mediumCount' => $studentsWithRisk->where('risk_level', 'medium')->count(),
            'totalStudents' => $studentsProgress->count(),
        ]));
    }

    protected function renderAveragesReport($studentsProgress, $viewData)
    {
        $statistics = $this->statisticsCalculator->calculateBasicStats($studentsProgress);
        $assignmentStats = $this->statisticsCalculator->calculateAssignmentStatistics($studentsProgress);
        $chartData = $this->chartDataGenerator->generateStudentPerformanceChart($studentsProgress);
        $trendData = $this->trendAnalyzer->calculateTrendData($studentsProgress);

        return view('results.averages-report', array_merge($viewData, [
            'totalStudents' => $statistics['total_students'],
            'totalAssignments' => $statistics['total_assignments'],
            'overallClassAverage' => $statistics['average_percentage'],
            'assignmentAnalysis' => $assignmentStats,
            'chartData' => $chartData,
            'trendData' => $trendData,
            'topPerformers' => collect(),
            'lowPerformers' => collect(),
            'insights' => ['performance' => [], 'assignments' => []],
        ]));
    }
}
