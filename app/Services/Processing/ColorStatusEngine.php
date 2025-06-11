<?php

namespace App\Services\Processing;

use App\Configuration\ReportConfiguration;

class ColorStatusEngine
{
    public function calculate(array $assignment, ?array $submission, ReportConfiguration $config): array
    {
        $status = $submission['workflow_state'] ?? 'unsubmitted';
        $score = $submission['score'] ?? 0;
        $pointsPossible = $assignment['points_possible'] ?? 1;

        // Check if excused
        if ($submission['excused'] ?? false) {
            return $this->createResult('bg-purple-200', 'excused', $this->getExcusedDisplay($config));
        }

        // Check if graded with score
        if ($status === 'graded' && $score !== null && $pointsPossible > 0) {
            return $this->calculateGradedResult($score, $pointsPossible, $config);
        }

        // Check submission status
        if ($status === 'submitted') {
            return $this->createResult('bg-blue-200', 'submitted', $this->getSubmittedDisplay($config));
        }

        // Check if non-submittable
        $submissionTypes = $assignment['submission_types'] ?? [];
        if (empty($submissionTypes) || in_array('none', $submissionTypes)) {
            return $this->createResult('bg-gray-300', 'non_submittable', $this->getNonSubmittableDisplay($config));
        }

        // Default: not submitted
        return $this->createResult('bg-orange-200', 'unsubmitted', $this->getUnsubmittedDisplay($config));
    }

    protected function calculateGradedResult(float $score, float $pointsPossible, ReportConfiguration $config): array
    {
        $percentage = ($score / $pointsPossible) * 100;

        if ($percentage >= $config->getGoodThreshold()) {
            return $this->createResult('bg-green-200', 'good', $this->getGoodDisplay($config, $percentage, $score));
        }

        if ($percentage >= $config->getSufficientThreshold()) {
            return $this->createResult('bg-yellow-200', 'sufficient', $this->getSufficientDisplay($config, $percentage, $score));
        }

        return $this->createResult('bg-red-200', 'insufficient', $this->getInsufficientDisplay($config, $percentage, $score));
    }

    protected function createResult(string $color, string $status, string $displayValue): array
    {
        return [
            'color' => $color,
            'status' => $status,
            'display_value' => $displayValue
        ];
    }

    protected function getGoodDisplay(ReportConfiguration $config, float $percentage, float $score): string
    {
        return match ($config->reportType) {
            'basic' => $config->displayFormats['good'] ?? 'Goed',
            'grades' => number_format($score, $config->displayFormats['decimals'] ?? 1),
            'percentages', 'averages' => number_format($percentage, 0) . '%',
            default => 'Goed'
        };
    }

    protected function getSufficientDisplay(ReportConfiguration $config, float $percentage, float $score): string
    {
        return match ($config->reportType) {
            'basic' => $config->displayFormats['sufficient'] ?? 'Voldoende',
            'grades' => number_format($score, $config->displayFormats['decimals'] ?? 1),
            'percentages', 'averages' => number_format($percentage, 0) . '%',
            default => 'Voldoende'
        };
    }

    protected function getInsufficientDisplay(ReportConfiguration $config, float $percentage, float $score): string
    {
        return match ($config->reportType) {
            'basic' => $config->displayFormats['insufficient'] ?? 'Onvoldoende',
            'grades' => number_format($score, $config->displayFormats['decimals'] ?? 1),
            'percentages', 'averages' => number_format($percentage, 0) . '%',
            default => 'Onvoldoende'
        };
    }

    protected function getSubmittedDisplay(ReportConfiguration $config): string
    {
        return match ($config->reportType) {
            'basic' => $config->displayFormats['submitted'] ?? 'Ingeleverd',
            'attention' => 'Nakijken',
            default => ''
        };
    }

    protected function getUnsubmittedDisplay(ReportConfiguration $config): string
    {
        return match ($config->reportType) {
            'basic' => $config->displayFormats['missing'] ?? '',
            'missing' => $config->displayFormats['missing'] ?? '',
            'attention' => 'Hulp nodig',
            default => ''
        };
    }

    protected function getExcusedDisplay(ReportConfiguration $config): string
    {
        return $config->reportType === 'basic' ? 'Vrijgesteld' : '';
    }

    protected function getNonSubmittableDisplay(ReportConfiguration $config): string
    {
        return $config->reportType === 'basic' ? 'Geen inlevering' : '';
    }
}
