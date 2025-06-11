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
                // Behoud alle originele data, pas alleen display_value aan
                $assignmentCopy = $assignment;
                $assignmentCopy['display_value'] = $this->convertToPercentageDisplay($assignment);
                return $assignmentCopy;
            });

            // Bereken student gemiddelde voor percentage rapport
            $averagePercentage = $this->calculateStudentAveragePercentage($student);
            $gradedCount = $this->countGradedAssignments($student);
            $totalAssignments = $student['assignments']->count();

            return array_merge($student, [
                'assignments' => $processedAssignments, // Gebruik assignments in plaats van processed_assignments
                'average_percentage' => $averagePercentage,
                'graded_count' => $gradedCount,
                'total_assignments' => $totalAssignments
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

        // Voor alle andere statussen, gebruik de originele display_value
        return $assignment['display_value'] ?? '';
    }

    private function calculateStudentAveragePercentage(array $student): float
    {
        // Filter assignments die een numerieke score hebben
        $gradedAssignments = $student['assignments']->filter(function ($assignment) {
            return isset($assignment['score']) &&
                is_numeric($assignment['score']) &&
                isset($assignment['points_possible']) &&
                $assignment['points_possible'] > 0;
        });

        if ($gradedAssignments->isEmpty()) {
            return 0;
        }

        // Bereken totaal behaalde punten en totaal mogelijke punten
        $totalScore = $gradedAssignments->sum('score');
        $totalPossible = $gradedAssignments->sum('points_possible');

        return $totalPossible > 0 ? round(($totalScore / $totalPossible) * 100, 1) : 0;
    }

    private function countGradedAssignments(array $student): int
    {
        return $student['assignments']->filter(function ($assignment) {
            return isset($assignment['score']) &&
                is_numeric($assignment['score']) &&
                isset($assignment['points_possible']) &&
                $assignment['points_possible'] > 0;
        })->count();
    }
}
