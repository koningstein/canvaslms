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
        // Gebruik de originele studentsProgress voor consistente kleuren
        $studentsWithPercentages = $studentsProgress->map(function ($student) {
            // Converteer assignments naar percentage display
            $processedAssignments = $student['assignments']->map(function ($assignment) {
                // Behoud alle originele data, pas alleen display_value aan voor percentages
                $assignmentCopy = $assignment;
                $assignmentCopy['display_value'] = $this->convertToPercentageDisplay($assignment);
                return $assignmentCopy;
            });

            // Bereken student gemiddelde voor percentage rapport
            $averagePercentage = $this->calculateStudentAveragePercentage($student);
            $gradedCount = $this->countGradedAssignments($student);
            $totalPointsAssignments = $this->countPointsAssignments($student); // Alleen opdrachten met punten

            return array_merge($student, [
                'assignments' => $processedAssignments, // Gebruik assignments in plaats van processed_assignments
                'average_percentage' => $averagePercentage,
                'graded_count' => $gradedCount,
                'total_assignments' => $totalPointsAssignments // Gebruik opdrachten met punten
            ]);
        });

        // Groepeer assignments voor headers (zoals in andere rapporten)
        $assignmentGroups = [];
        if ($studentsWithPercentages->isNotEmpty()) {
            $assignmentGroups = $studentsWithPercentages->first()['assignments']->groupBy('module_name');
        }

        return [
            'studentsWithPercentages' => $studentsWithPercentages,
            'assignmentGroups' => $assignmentGroups
        ];
    }

    private function convertToPercentageDisplay(array $assignment): string
    {
        $status = $assignment['status'] ?? 'unknown';
        $score = $assignment['score'] ?? 0;
        $pointsPossible = $assignment['points_possible'] ?? 0;

        // Voor percentage rapport: toon percentages voor beoordeelde opdrachten
        if ($status === 'graded' && $score !== null && $pointsPossible > 0) {
            $percentage = ($score / $pointsPossible) * 100;
            return round($percentage, 0) . '%';
        }

        // Voor niet-ingeleverde opdrachten: geen tekst, alleen kleur
        if ($status === 'unsubmitted' || $status === 'missing') {
            return '';
        }

        // Voor alle andere statussen, gebruik de originele display_value (zoals "Ingeleverd", "Vrijgesteld")
        return $assignment['display_value'] ?? '';
    }

    private function calculateStudentAveragePercentage(array $student): float
    {
        $totalPercentage = 0;
        $totalAssignments = 0;

        foreach ($student['assignments'] as $assignment) {
            // Alleen opdrachten met punten meetellen
            if (isset($assignment['points_possible']) && $assignment['points_possible'] > 0) {
                $totalAssignments++;

                // Als beoordeeld: gebruik het behaalde percentage
                if (isset($assignment['score']) && is_numeric($assignment['score'])) {
                    $percentage = ($assignment['score'] / $assignment['points_possible']) * 100;
                    $totalPercentage += $percentage;
                }
                // Als niet beoordeeld: tel als 0%
                // (totalPercentage += 0, dus geen wijziging)
            }
        }

        return $totalAssignments > 0 ? round($totalPercentage / $totalAssignments, 1) : 0;
    }

    private function countGradedAssignments(array $student): int
    {
        return $student['assignments']->filter(function ($assignment) {
            // Een assignment is beoordeeld als het een score EN punten mogelijk heeft
            // De status kan 'graded', 'good', 'sufficient', 'insufficient' zijn
            $hasValidScore = isset($assignment['score']) &&
                is_numeric($assignment['score']) &&
                isset($assignment['points_possible']) &&
                $assignment['points_possible'] > 0;

            // Check ook de status om te zien of het echt beoordeeld is
            $status = $assignment['status'] ?? '';
            $isGraded = in_array($status, ['graded', 'good', 'sufficient', 'insufficient']);

            return $hasValidScore && $isGraded;
        })->count();
    }

    private function countPointsAssignments(array $student): int
    {
        return $student['assignments']->filter(function ($assignment) {
            return isset($assignment['points_possible']) && $assignment['points_possible'] > 0;
        })->count();
    }
}
