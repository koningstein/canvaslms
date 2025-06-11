<?php

namespace App\Http\Controllers;

use App\Configuration\ReportConfigurationFactory;
use App\Services\Processing\ConfigurableReportProcessor;
use App\Services\Analyzers\PerformanceAnalyzer;
use App\Services\Analyzers\TrendAnalyzer;
use App\Services\Analyzers\StatisticsCalculator;
use App\Services\Analyzers\ChartDataGenerator;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ConfigurableReportProcessor $reportProcessor,
        protected PerformanceAnalyzer $performanceAnalyzer,
        protected TrendAnalyzer $trendAnalyzer,
        protected StatisticsCalculator $statisticsCalculator,
        protected ChartDataGenerator $chartDataGenerator
    ) {}

    public function getSelectedProgress()
    {
        $students = session('selected_users', []);
        $courseModules = session('selected_modules', []);
        $reportType = session('report_type', 'basic');

        $config = ReportConfigurationFactory::create($reportType);
        $studentsProgress = $this->reportProcessor->processReport($students, $courseModules, $config);

        return $this->renderReport($studentsProgress, $config);
    }

    protected function renderReport($studentsProgress, $config)
    {
        $viewData = ['studentsProgress' => $studentsProgress, 'reportType' => $config->reportType];

        switch ($config->reportType) {
            case 'attention':
                return $this->renderAttentionReport($studentsProgress, $viewData);
            case 'averages':
                return $this->renderAveragesReport($studentsProgress, $viewData);
            default:
                return view("results.{$config->reportType}-report", $viewData);
        }
    }

    protected function renderAttentionReport($studentsProgress, $viewData)
    {
        $studentsWithRisk = $this->performanceAnalyzer->calculateAttentionRisks($studentsProgress);

        return view('results.attention-report', array_merge($viewData, [
            'studentsWithRisk' => $studentsWithRisk,
            'urgentCount' => $studentsWithRisk->where('risk_level', 'urgent')->count(),
            'highCount' => $studentsWithRisk->where('risk_level', 'high')->count(),
            'mediumCount' => $studentsWithRisk->where('risk_level', 'medium')->count(),
        ]));
    }

    protected function renderAveragesReport($studentsProgress, $viewData)
    {
        $statistics = $this->statisticsCalculator->calculateBasicStats($studentsProgress);
        $assignmentStats = $this->statisticsCalculator->calculateAssignmentStatistics($studentsProgress);
        $chartData = $this->chartDataGenerator->generateStudentPerformanceChart($studentsProgress);
        $trendData = $this->trendAnalyzer->calculateTrendData($studentsProgress);

        return view('results.averages-report', array_merge($viewData, [
            'statistics' => $statistics,
            'assignmentAnalysis' => $assignmentStats,
            'chartData' => $chartData,
            'trendData' => $trendData,
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
            // Statistics
            'totalStudents' => $statistics['total_students'],
            'totalAssignments' => $statistics['total_assignments'],

            // Grades specific data
            'totalPointsAwarded' => $gradesData['totalPointsAwarded'],
            'totalPointsPossible' => $gradesData['totalPointsPossible'],
            'averagePercentage' => $gradesData['averagePercentage'],
            'assignmentGroups' => $gradesData['assignmentGroups'],
            'studentsWithScores' => $gradesData['studentsWithScores'],

            // Averages
            'assignmentAverages' => $assignmentAverages,
            'classAverageData' => $classAverageData,
        ]));
    }
}
