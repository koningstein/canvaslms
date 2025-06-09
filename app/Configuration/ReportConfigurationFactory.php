<?php

namespace App\Configuration;

class ReportConfigurationFactory
{
    public static function create(string $reportType): ReportConfiguration
    {
        return match ($reportType) {
            'basic' => self::createBasicConfig(),
            'grades' => self::createGradesConfig(),
            'percentages' => self::createPercentagesConfig(),
            'missing' => self::createMissingConfig(),
            'attention' => self::createAttentionConfig(),
            'averages' => self::createAveragesConfig(),
            default => self::createBasicConfig(),
        };
    }

    private static function createBasicConfig(): ReportConfiguration
    {
        return new ReportConfiguration(
            reportType: 'basic',
            colorThresholds: ['good' => 75.0, 'sufficient' => 55.0],
            displayFormats: ['good' => 'Goed', 'sufficient' => 'Voldoende', 'insufficient' => 'Onvoldoende', 'submitted' => 'Ingeleverd', 'missing' => 'Niet ingeleverd'],
            showElements: ['percentages' => false, 'scores' => false, 'only_problematic' => false],
            aggregationRules: ['show_totals' => true, 'calculate_averages' => false]
        );
    }

    private static function createGradesConfig(): ReportConfiguration
    {
        return new ReportConfiguration(
            reportType: 'grades',
            colorThresholds: ['good' => 75.0, 'sufficient' => 55.0],
            displayFormats: ['show_numeric' => true, 'decimals' => 1],
            showElements: ['percentages' => false, 'scores' => true, 'totals' => true],
            aggregationRules: ['show_totals' => true, 'calculate_averages' => true]
        );
    }

    private static function createPercentagesConfig(): ReportConfiguration
    {
        return new ReportConfiguration(
            reportType: 'percentages',
            colorThresholds: ['good' => 75.0, 'sufficient' => 55.0],
            displayFormats: ['format' => 'percentage', 'decimals' => 0],
            showElements: ['percentages' => true, 'averages' => true],
            aggregationRules: ['show_totals' => true, 'calculate_averages' => true]
        );
    }

    private static function createMissingConfig(): ReportConfiguration
    {
        return new ReportConfiguration(
            reportType: 'missing',
            colorThresholds: ['good' => 75.0, 'sufficient' => 55.0],
            displayFormats: ['missing' => 'Ontbreekt', 'insufficient' => 'Onvoldoende', 'late' => 'Te laat'],
            showElements: ['only_problematic' => true, 'late_submissions' => true],
            aggregationRules: ['count_missing' => true, 'count_insufficient' => true]
        );
    }

    private static function createAttentionConfig(): ReportConfiguration
    {
        return new ReportConfiguration(
            reportType: 'attention',
            colorThresholds: ['urgent' => 5, 'high' => 3, 'medium' => 1],
            displayFormats: ['urgent' => 'Hulp nodig', 'high' => 'Extra begeleiding', 'medium' => 'Aandacht'],
            showElements: ['risk_scores' => true, 'detailed_lists' => true],
            aggregationRules: ['calculate_risk' => true, 'smart_weighting' => true]
        );
    }

    private static function createAveragesConfig(): ReportConfiguration
    {
        return new ReportConfiguration(
            reportType: 'averages',
            colorThresholds: ['good' => 75.0, 'sufficient' => 55.0],
            displayFormats: ['format' => 'percentage', 'show_charts' => true],
            showElements: ['averages' => true, 'trends' => true, 'insights' => true],
            aggregationRules: ['calculate_averages' => true, 'generate_charts' => true, 'analyze_trends' => true]
        );
    }
}
