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
}
