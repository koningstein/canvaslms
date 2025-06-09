<?php

namespace App\Services\Processing;

class MissingReportProcessor
{
    /**
     * Process students data specifically for missing report
     * Filters only students with problems and transforms their assignment data
     */
    public function processMissingData($studentsProgress)
    {
        // Filter studenten met problemen
        $studentsWithProblems = $studentsProgress->filter(function($student) {
            return $student['assignments']->whereIn('display_value', ['Ontbreekt', 'Onvoldoende', 'Te laat'])->count() > 0;
        })->map(function($student) {
            // Voeg probleem count toe aan elke student
            $problemCount = $student['assignments']->whereIn('display_value', ['Ontbreekt', 'Onvoldoende', 'Te laat'])->count();

            // Process assignments voor missing report (alleen problematische tonen)
            $processedAssignments = $student['assignments']->map(function($assignment) {
                $displayValue = $assignment['display_value'] ?? '';
                $isProblematic = in_array($displayValue, ['Ontbreekt', 'Onvoldoende', 'Te laat']);

                return [
                    'assignment_name' => $assignment['assignment_name'],
                    'display_value' => $isProblematic ? $displayValue : '',
                    'cell_color' => $isProblematic ? $this->getProblematicColor($displayValue) : 'bg-white',
                    'tooltip' => $assignment['assignment_name'] . ' - ' . $displayValue,
                    'show_late_icon' => $displayValue === 'Te laat',
                ];
            });

            return array_merge($student, [
                'problem_count' => $problemCount,
                'processed_assignments' => $processedAssignments
            ]);
        });

        // Groepeer assignments per module (nodig voor tabel headers)
        $assignmentGroups = [];
        if ($studentsProgress->isNotEmpty()) {
            $assignmentGroups = $studentsProgress->first()['assignments']->groupBy('module_name');
        }

        return [
            'studentsWithProblems' => $studentsWithProblems,
            'assignmentGroups' => $assignmentGroups,
        ];
    }

    /**
     * Get consistent colors for problematic assignments
     */
    protected function getProblematicColor($displayValue)
    {
        return match($displayValue) {
            'Ontbreekt' => 'bg-orange-200',
            'Onvoldoende' => 'bg-red-200',
            'Te laat' => 'bg-yellow-200',
            default => 'bg-gray-100'
        };
    }
}
