<?php

namespace App\Services\Processing;

use App\Services\Analyzers\StatisticsCalculator;
use Illuminate\Support\Collection;

class PercentagesReportProcessor
{
    public function __construct(
        protected GradesReportProcessor $gradesReportProcessor
    ) {}

    public function processPercentagesData(Collection $studentsProgress): array
    {
        // Hergebruik de volledige GradesReportProcessor pipeline
        $gradesData = $this->gradesReportProcessor->processGradesData($studentsProgress);

        // Converteer alleen de display values naar percentages
        $studentsWithPercentages = collect($gradesData['studentsWithScores'])->map(function ($student) {
            $processedAssignments = collect($student['processed_assignments'])->map(function ($assignment) {
                return array_merge($assignment, [
                    'display_value' => $this->convertToPercentageDisplay($assignment)
                ]);
            })->toArray();

            return array_merge($student, [
                'processed_assignments' => $processedAssignments
            ]);
        })->toArray();

        return [
            'studentsWithPercentages' => $studentsWithPercentages,
            'assignmentGroups' => $gradesData['assignmentGroups'] ?? []
        ];
    }

    private function convertToPercentageDisplay(array $assignment): string
    {
        // Als er een percentage beschikbaar is, toon dat
        if (isset($assignment['percentage']) && $assignment['percentage'] !== null) {
            return $assignment['percentage'] . '%';
        }

        // Anders gebruik de bestaande display value van GradesReportProcessor
        return $assignment['display_value'];
    }
}
