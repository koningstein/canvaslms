<?php

namespace App\Configuration;

class ReportConfiguration
{
    public function __construct(
        public readonly string $reportType,
        public readonly array $colorThresholds,
        public readonly array $displayFormats,
        public readonly array $showElements,
        public readonly array $aggregationRules
    ) {}

    public function getGoodThreshold(): float
    {
        return $this->colorThresholds['good'] ?? 75.0;
    }

    public function getSufficientThreshold(): float
    {
        return $this->colorThresholds['sufficient'] ?? 55.0;
    }

    public function shouldShowPercentages(): bool
    {
        return $this->showElements['percentages'] ?? false;
    }

    public function shouldShowScores(): bool
    {
        return $this->showElements['scores'] ?? false;
    }

    public function shouldShowOnlyProblematic(): bool
    {
        return $this->showElements['only_problematic'] ?? false;
    }
}
