<?php

namespace App\Services\Analyzers;

use Illuminate\Support\Collection;

class AverageCalculator
{
    /**
     * Bereken gemiddelde scores per student
     */
    public function calculateStudentAverages(Collection $studentsProgress): Collection
    {
        return $studentsProgress->map(function ($student) {
            $assignments = $student['assignments'];

            // Filter assignments die een numerieke score hebben
            $gradedAssignments = $assignments->filter(function ($assignment) {
                return isset($assignment['score']) &&
                    is_numeric($assignment['score']) &&
                    isset($assignment['points_possible']) &&
                    $assignment['points_possible'] > 0;
            });

            if ($gradedAssignments->isEmpty()) {
                return array_merge($student, [
                    'average_score' => null,
                    'average_percentage' => null,
                    'graded_count' => 0,
                    'total_assignments' => $assignments->count(),
                    'average_display' => 'Geen cijfers',
                    'average_color' => 'bg-gray-200'
                ]);
            }

            // Bereken totaal behaalde punten en totaal mogelijke punten
            $totalScore = $gradedAssignments->sum('score');
            $totalPossible = $gradedAssignments->sum('points_possible');

            $averagePercentage = $totalPossible > 0 ? round(($totalScore / $totalPossible) * 100, 1) : 0;

            // Bepaal kleur op basis van percentage
            $averageColor = $this->getColorForPercentage($averagePercentage);

            return array_merge($student, [
                'average_score' => round($totalScore, 1),
                'average_percentage' => $averagePercentage,
                'graded_count' => $gradedAssignments->count(),
                'total_assignments' => $assignments->count(),
                'average_display' => $averagePercentage . '%',
                'average_color' => $averageColor
            ]);
        });
    }

    /**
     * Bereken gemiddelde scores per opdracht
     */
    public function calculateAssignmentAverages(Collection $studentsProgress): Collection
    {
        if ($studentsProgress->isEmpty()) {
            return collect();
        }

        // Verzamel alle unieke opdrachten
        $allAssignments = $studentsProgress->first()['assignments'];

        return $allAssignments->map(function ($assignment) use ($studentsProgress) {
            $assignmentName = $assignment['assignment_name'];

            // Verzamel scores voor deze opdracht van alle studenten
            $scores = $studentsProgress->map(function ($student) use ($assignmentName) {
                return $student['assignments']->where('assignment_name', $assignmentName)->first();
            })->filter(function ($assignment) {
                return $assignment &&
                    isset($assignment['score']) &&
                    is_numeric($assignment['score']) &&
                    isset($assignment['points_possible']) &&
                    $assignment['points_possible'] > 0;
            });

            if ($scores->isEmpty()) {
                return array_merge($assignment, [
                    'average_score' => null,
                    'average_percentage' => null,
                    'graded_count' => 0,
                    'total_students' => $studentsProgress->count(),
                    'average_display' => '-',
                    'average_color' => 'bg-gray-200'
                ]);
            }

            // Bereken gemiddelde
            $totalScore = $scores->sum('score');
            $totalPossible = $scores->sum('points_possible');
            $averagePercentage = $totalPossible > 0 ? round(($totalScore / $totalPossible) * 100, 1) : 0;

            // Bepaal kleur
            $averageColor = $this->getColorForPercentage($averagePercentage);

            return array_merge($assignment, [
                'average_score' => round($totalScore / $scores->count(), 1),
                'average_percentage' => $averagePercentage,
                'graded_count' => $scores->count(),
                'total_students' => $studentsProgress->count(),
                'average_display' => $averagePercentage . '%',
                'average_color' => $averageColor
            ]);
        });
    }

    /**
     * Bereken klas gemiddelde
     */
    public function calculateClassAverage(Collection $studentsProgress): array
    {
        $studentsWithAverages = $this->calculateStudentAverages($studentsProgress);

        $studentsWithScores = $studentsWithAverages->filter(function ($student) {
            return $student['average_percentage'] !== null;
        });

        if ($studentsWithScores->isEmpty()) {
            return [
                'class_average' => null,
                'class_average_display' => 'Geen cijfers',
                'class_average_color' => 'bg-gray-200',
                'students_with_grades' => 0,
                'total_students' => $studentsProgress->count()
            ];
        }

        $classAverage = round($studentsWithScores->avg('average_percentage'), 1);

        return [
            'class_average' => $classAverage,
            'class_average_display' => $classAverage . '%',
            'class_average_color' => $this->getColorForPercentage($classAverage),
            'students_with_grades' => $studentsWithScores->count(),
            'total_students' => $studentsProgress->count()
        ];
    }

    /**
     * Bepaal kleur op basis van percentage
     */
    protected function getColorForPercentage($percentage): string
    {
        if ($percentage >= 75) {
            return 'bg-green-200 text-green-800';
        } elseif ($percentage >= 55) {
            return 'bg-yellow-200 text-yellow-800';
        } else {
            return 'bg-red-200 text-red-800';
        }
    }
}
