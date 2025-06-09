<?php

namespace App\Services\Processing;

use App\Configuration\ReportConfiguration;

class ResultFormatterEngine
{
    public function format(
        array $assignment,
        ?array $submission,
        array $colorStatus,
        ReportConfiguration $config
    ): array {
        $baseResult = [
            'assignment_name' => $assignment['assignment_name'],
            'assignment_id' => $assignment['assignment_id'],
            'module_id' => $assignment['module_id'],
            'module_name' => $assignment['module_name'],
            'status' => $colorStatus['status'],
            'color' => $colorStatus['color'],
            'display_value' => $colorStatus['display_value'],
            'score' => $submission['score'] ?? 0,
            'points_possible' => $assignment['points_possible'] ?? 0,
            'submitted_at' => $submission['submitted_at'] ?? null,
            'graded_at' => $submission['graded_at'] ?? null,
            'due_at' => $assignment['due_at'] ?? null,
            'grade' => $submission['grade'] ?? '',
            'excused' => $submission['excused'] ?? false,
            'submission_types' => $assignment['submission_types'] ?? [],
            'assignment_group_name' => $assignment['assignment_group_name'] ?? 'Unknown',
        ];

        // Add report-specific formatting
        return $this->addReportSpecificFormatting($baseResult, $config);
    }

    protected function addReportSpecificFormatting(array $result, ReportConfiguration $config): array
    {
        switch ($config->reportType) {
            case 'grades':
                return $this->formatForGrades($result, $config);
            case 'percentages':
                return $this->formatForPercentages($result, $config);
            case 'missing':
                return $this->formatForMissing($result, $config);
            case 'attention':
                return $this->formatForAttention($result, $config);
            default:
                return $result;
        }
    }

    protected function formatForGrades(array $result, ReportConfiguration $config): array
    {
        if ($result['status'] === 'graded' && $result['points_possible'] > 0) {
            $result['show_points_possible'] = true;
            $result['tooltip'] = $this->createGradesTooltip($result);
        }

        return $result;
    }

    protected function formatForPercentages(array $result, ReportConfiguration $config): array
    {
        if ($result['status'] === 'graded' && $result['points_possible'] > 0) {
            $percentage = ($result['score'] / $result['points_possible']) * 100;
            $result['tooltip'] = $this->createPercentageTooltip($result, $percentage);
        }

        return $result;
    }

    protected function formatForMissing(array $result, ReportConfiguration $config): array
    {
        // Only show if problematic
        $isProblematic = in_array($result['display_value'], ['Ontbreekt', 'Onvoldoende', 'Te laat']);

        if (!$isProblematic) {
            $result['color'] = 'bg-white';
            $result['display_value'] = '';
        }

        return $result;
    }

    protected function formatForAttention(array $result, ReportConfiguration $config): array
    {
        // Simplified display for attention report
        if (!in_array($result['status'], ['unsubmitted', 'graded', 'submitted'])) {
            $result['color'] = 'bg-white';
            $result['display_value'] = '';
        }

        return $result;
    }

    protected function createGradesTooltip(array $result): string
    {
        $percentage = round(($result['score'] / $result['points_possible']) * 100, 1);
        return "{$result['assignment_name']} - {$result['score']}/{$result['points_possible']} punten ({$percentage}%)";
    }

    protected function createPercentageTooltip(array $result, float $percentage): string
    {
        return "{$result['assignment_name']} - " . number_format($percentage, 1) . "% ({$result['score']}/{$result['points_possible']} punten)";
    }
}
