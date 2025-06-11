<?php

namespace App\Services\Processing;

use Illuminate\Support\Collection;

class GradesReportProcessor
{
    public function processGradesData(Collection $studentsProgress): array
    {
        $studentsWithScores = [];
        $totalPointsAwarded = 0;
        $totalPointsPossible = 0;

        foreach ($studentsProgress as $student) {
            $processedAssignments = [];
            $studentTotalScore = 0;
            $studentTotalPossible = 0;

            foreach ($student['assignments'] as $assignment) {
                $pointsAwarded = $assignment['score'] ?? 0;
                $pointsPossible = $assignment['points_possible'] ?? 0;

                $processed = $this->processGradeAssignment($assignment, $pointsAwarded, $pointsPossible);
                $processedAssignments[] = $processed;

                // Tel ALLE opdrachten met punten mee voor "mogelijk"
                if ($pointsPossible > 0) {
                    $studentTotalPossible += $pointsPossible;
                    $totalPointsPossible += $pointsPossible;

                    // Tel alleen behaalde punten mee als beoordeeld
                    $isGraded = in_array($assignment['status'], ['graded', 'good', 'sufficient', 'insufficient']);
                    if (isset($assignment['score']) && is_numeric($assignment['score']) && $isGraded) {
                        $studentTotalScore += $pointsAwarded;
                        $totalPointsAwarded += $pointsAwarded;
                    }
                    // Als niet beoordeeld: 0 punten behaald, maar wel meetellen voor mogelijk
                }
            }

            $studentPercentage = $studentTotalPossible > 0
                ? round(($studentTotalScore / $studentTotalPossible) * 100, 1)
                : 0;

            // BELANGRIJKE FIX: Behoud de originele student structuur + voeg extra data toe
            $studentsWithScores[] = array_merge($student, [
                'processed_assignments' => $processedAssignments,
                'total_score' => round($studentTotalScore, 1),
                'total_possible' => round($studentTotalPossible, 1),
                'total_percentage' => $studentPercentage
            ]);
        }

        $averagePercentage = $totalPointsPossible > 0
            ? round(($totalPointsAwarded / $totalPointsPossible) * 100, 1)
            : 0;

        // Groepeer assignments voor table headers
        $assignmentGroups = [];
        if (!empty($studentsWithScores)) {
            $firstStudent = $studentsWithScores[0];
            if (isset($firstStudent['assignments']) && $firstStudent['assignments']->isNotEmpty()) {
                $assignmentGroups = $firstStudent['assignments']->groupBy('module_name');
            }
        }

        return [
            'studentsWithScores' => collect($studentsWithScores),
            'totalPointsAwarded' => round($totalPointsAwarded, 1),
            'totalPointsPossible' => round($totalPointsPossible, 1),
            'averagePercentage' => $averagePercentage,
            'assignmentGroups' => $assignmentGroups
        ];
    }

    private function processGradeAssignment($assignment, $pointsAwarded, $pointsPossible): array
    {
        $status = $assignment['status'] ?? 'not_submitted';
        $percentage = $pointsPossible > 0 ? ($pointsAwarded / $pointsPossible) * 100 : 0;

        // Bepaal display value en kleur voor grades report
        if ($status === 'unsubmitted' || $status === 'missing') {
            $displayValue = ''; // Geen tekst, alleen kleur
            $color = 'bg-orange-200';
            $showPointsPossible = false;
            $tooltip = $assignment['assignment_name'] . ' - Niet ingeleverd';
        } elseif ($status === 'submitted' && !isset($assignment['score'])) {
            $displayValue = ''; // Geen tekst, alleen kleur
            $color = 'bg-blue-200';
            $showPointsPossible = false;
            $tooltip = $assignment['assignment_name'] . ' - Ingeleverd, nog niet beoordeeld';
        } elseif ($status === 'graded' && isset($assignment['score']) && $pointsPossible > 0) {
            $displayValue = $pointsAwarded; // Toon de score (bijv. 9.0, 13.0)
            $showPointsPossible = true;
            $tooltip = $assignment['assignment_name'] . " - {$pointsAwarded}/{$pointsPossible} punten (" . round($percentage, 1) . "%)";

            // Kleur op basis van percentage
            if ($percentage >= 75) {
                $color = 'bg-green-200';
            } elseif ($percentage >= 55) {
                $color = 'bg-yellow-200';
            } else {
                $color = 'bg-red-200';
            }
        } elseif ($status === 'excused') {
            $displayValue = ''; // Geen tekst, alleen kleur
            $color = 'bg-purple-200';
            $showPointsPossible = false;
            $tooltip = $assignment['assignment_name'] . ' - Vrijgesteld';
        } else {
            $displayValue = ''; // Geen tekst, alleen kleur
            $color = 'bg-gray-100';
            $showPointsPossible = false;
            $tooltip = $assignment['assignment_name'] . ' - Geen data beschikbaar';
        }

        return [
            'assignment_name' => $assignment['assignment_name'],
            'module_name' => $assignment['module_name'],
            'display_value' => $displayValue,
            'color' => $color,
            'show_points_possible' => $showPointsPossible,
            'points_possible' => $pointsPossible,
            'tooltip' => $tooltip,
            'status' => $status,
            'score' => $pointsAwarded,
            'percentage' => round($percentage, 1)
        ];
    }

    public function groupAssignmentsByModule($assignmentStats): Collection
    {
        return collect($assignmentStats)->groupBy('module_name');
    }
}
