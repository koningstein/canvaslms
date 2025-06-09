<?php

namespace App\Providers;

use App\Configuration\ReportConfigurationFactory;
use App\Services\Processing\ConfigurableReportProcessor;
use App\Services\Processing\DataAggregationEngine;
use App\Services\Processing\ColorStatusEngine;
use App\Services\Processing\ResultFormatterEngine;
use App\Services\Data\StudentDataExtractor;
use App\Services\Data\AssignmentDataExtractor;
use App\Services\Data\SubmissionDataExtractor;
use App\Services\Data\ModuleDataExtractor;
use App\Services\Analyzers\PerformanceAnalyzer;
use App\Services\Analyzers\TrendAnalyzer;
use App\Services\Analyzers\StatisticsCalculator;
use App\Services\Analyzers\ChartDataGenerator;
use Illuminate\Support\ServiceProvider;

class ReportServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Data Services
        $this->app->singleton(StudentDataExtractor::class);
        $this->app->singleton(AssignmentDataExtractor::class);
        $this->app->singleton(SubmissionDataExtractor::class);
        $this->app->singleton(ModuleDataExtractor::class);

        // Processing Engine
        $this->app->singleton(ColorStatusEngine::class);
        $this->app->singleton(ResultFormatterEngine::class);
        $this->app->singleton(DataAggregationEngine::class);
        $this->app->singleton(ConfigurableReportProcessor::class);

        // Analyzers
        $this->app->singleton(PerformanceAnalyzer::class);
        $this->app->singleton(TrendAnalyzer::class);
        $this->app->singleton(StatisticsCalculator::class);
        $this->app->singleton(ChartDataGenerator::class);
    }

    public function boot()
    {
        //
    }
}
