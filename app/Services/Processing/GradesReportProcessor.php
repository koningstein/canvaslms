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

                if ($pointsPossible > 0 && $assignment['status'] === 'graded') {
                    $studentTotalScore += $pointsAwarded;
                    $studentTotalPossible += $pointsPossible;
                    $totalPointsAwarded += $pointsAwarded;
                    $totalPointsPossible += $pointsPossible;
                }
            }

            $studentPercentage = $studentTotalPossible > 0
                ? round(($studentTotalScore / $studentTotalPossible) * 100, 1)
                : 0;

            // BELANGRIJKE FIX: Behoud de originele student structuur + voeg extra data toe
            $studentsWithScores[] = array_merge($student, [
                'processed_assignments' => $processedAssignments,
                'total_score' => $studentTotalScore,
                'total_possible' => $studentTotalPossible,
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
            'totalPointsAwarded' => $totalPointsAwarded,
            'totalPointsPossible' => $totalPointsPossible,
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
            $displayValue = '-';
            $color = 'bg-orange-200';
            $showPointsPossible = false;
            $tooltip = $assignment['assignment_name'] . ' - Niet ingeleverd';
        } elseif ($status === 'submitted' && !isset($assignment['score'])) {
            $displayValue = 'Ingeleverd';
            $color = 'bg-blue-200';
            $showPointsPossible = false;
            $tooltip = $assignment['assignment_name'] . ' - Ingeleverd, nog niet beoordeeld';
        } elseif ($status === 'graded' && isset($assignment['score']) && $pointsPossible > 0) {
            $displayValue = $pointsAwarded;
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
            $displayValue = 'Vrijgesteld';
            $color = 'bg-purple-200';
            $showPointsPossible = false;
            $tooltip = $assignment['assignment_name'] . ' - Vrijgesteld';
        } else {
            $displayValue = 'Geen data';
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
