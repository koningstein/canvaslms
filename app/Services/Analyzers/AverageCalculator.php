<?php

namespace App\Services\Analyzers;

use Illuminate\Support\Collection;

class AverageCalculator
{
    /**
     * Bereken gemiddelde scores per student - AANGEPAST voor verschillende report types
     */
    public function calculateStudentAverages(Collection $studentsProgress, string $reportType = 'basic'): Collection
    {
        return $studentsProgress->map(function ($student) use ($reportType) {
            $assignments = $student['assignments'];

            if ($reportType === 'basic') {
                return $this->calculateBasicColorAverages($student, $assignments);
            } else {
                return $this->calculatePointBasedAverages($student, $assignments);
            }
        });
    }

    /**
     * Voor BASIC KLEUR rapport: tel opdrachten, niet punten
     */
    protected function calculateBasicColorAverages(array $student, Collection $assignments): array
    {
        // Filter alleen opdrachten die echt beoordeeld kunnen worden
        $assessableAssignments = $assignments->filter(function ($assignment) {
            // Een opdracht is beoordeelbaar als:
            // 1. Het punten heeft (points_possible > 0), OF
            // 2. Het een inleverbare opdracht is (submission_types niet leeg en niet 'none')
            $hasPoints = isset($assignment['points_possible']) && $assignment['points_possible'] > 0;
            $submissionTypes = $assignment['submission_types'] ?? [];
            $isSubmittable = !empty($submissionTypes) && !in_array('none', $submissionTypes);

            return $hasPoints || $isSubmittable;
        });

        // Filter alleen echt beoordeelde opdrachten (graded, good, sufficient, insufficient)
        $gradedAssignments = $assessableAssignments->filter(function ($assignment) {
            $status = $assignment['status'] ?? '';
            return in_array($status, ['graded', 'good', 'sufficient', 'insufficient']);
        });

        // Tel goede/voldoende opdrachten (good, sufficient)
        $goodAssignments = $gradedAssignments->filter(function ($assignment) {
            $status = $assignment['status'] ?? '';
            return in_array($status, ['good', 'sufficient']);
        });

        $totalAssessable = $assessableAssignments->count();
        $gradedCount = $gradedAssignments->count();
        $goodCount = $goodAssignments->count();

        // Bereken percentage op basis van aantal goede opdrachten vs ALLE beoordeelbare opdrachten
        $averagePercentage = $totalAssessable > 0 ? round(($goodCount / $totalAssessable) * 100, 1) : null;

        // Bepaal kleur op basis van percentage
        $averageColor = $this->getColorForBasicPercentage($averagePercentage);
        $averageDisplay = $averagePercentage !== null ? $averagePercentage . '%' : 'Geen cijfers';

        return array_merge($student, [
            'average_score' => null, // Niet relevant voor basic report
            'average_percentage' => $averagePercentage,
            'graded_count' => $gradedCount,
            'total_assignments' => $totalAssessable, // Alleen beoordeelbare opdrachten
            'good_count' => $goodCount,
            'average_display' => $averageDisplay,
            'average_color' => $averageColor
        ]);
    }

    /**
     * Voor PUNTEN-gebaseerde rapporten (grades, percentages)
     */
    protected function calculatePointBasedAverages(array $student, Collection $assignments): array
    {
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
    }

    /**
     * Bereken gemiddelde scores per opdracht - AANGEPAST
     */
    public function calculateAssignmentAverages(Collection $studentsProgress, string $reportType = 'basic'): Collection
    {
        if ($studentsProgress->isEmpty()) {
            return collect();
        }

        // Verzamel alle unieke opdrachten
        $allAssignments = $studentsProgress->first()['assignments'];

        return $allAssignments->map(function ($assignment) use ($studentsProgress, $reportType) {
            $assignmentName = $assignment['assignment_name'];

            if ($reportType === 'basic') {
                return $this->calculateBasicAssignmentAverage($assignment, $studentsProgress, $assignmentName);
            } else {
                return $this->calculatePointBasedAssignmentAverage($assignment, $studentsProgress, $assignmentName);
            }
        });
    }

    /**
     * Voor BASIC rapport: bereken percentage goed/voldoende per opdracht
     * MAAR gebruik wel de echte punten percentages, niet alleen status
     */
    protected function calculateBasicAssignmentAverage(array $assignment, Collection $studentsProgress, string $assignmentName): array
    {
        // Check of opdracht beoordeelbaar is
        $hasPoints = isset($assignment['points_possible']) && $assignment['points_possible'] > 0;
        $submissionTypes = $assignment['submission_types'] ?? [];
        $isSubmittable = !empty($submissionTypes) && !in_array('none', $submissionTypes);
        $isAssessable = $hasPoints || $isSubmittable;

        if (!$isAssessable) {
            return array_merge($assignment, [
                'average_score' => null,
                'average_percentage' => null,
                'graded_count' => 0,
                'total_students' => $studentsProgress->count(),
                'good_count' => 0,
                'average_display' => '-',
                'average_color' => 'bg-gray-300'
            ]);
        }

        $totalStudents = $studentsProgress->count();
        $totalPercentage = 0;
        $gradedCount = 0;
        $goodSufficientCount = 0;

        // Voor ALLE studenten: bereken hun percentage voor deze opdracht
        foreach ($studentsProgress as $student) {
            $response = $student['assignments']->where('assignment_name', $assignmentName)->first();

            if ($response) {
                $status = $response['status'] ?? '';

                if (in_array($status, ['graded', 'good', 'sufficient', 'insufficient'])) {
                    $gradedCount++;

                    // Als het punten heeft: gebruik echte percentage
                    if ($hasPoints && isset($response['score']) && isset($response['points_possible']) && $response['points_possible'] > 0) {
                        $percentage = ($response['score'] / $response['points_possible']) * 100;
                        $totalPercentage += $percentage;

                        // Tel als good/sufficient als percentage >= 55%
                        if ($percentage >= 55) {
                            $goodSufficientCount++;
                        }
                    } else {
                        // Voor opdrachten zonder punten: gebruik status
                        if (in_array($status, ['good', 'sufficient'])) {
                            $totalPercentage += 100; // Tel als 100%
                            $goodSufficientCount++;
                        } else {
                            $totalPercentage += 0; // Tel als 0%
                        }
                    }
                } else {
                    // Niet beoordeeld: tel altijd als 0% (ook voor niet-inleverbare opdrachten)
                    $totalPercentage += 0;
                }
            } else {
                // Geen response gevonden: tel als 0%
                $totalPercentage += 0;
            }
        }

        // Bereken gemiddelde percentage over ALLE studenten (ook niet-beoordeelde)
        $averagePercentage = $totalStudents > 0 ? round($totalPercentage / $totalStudents, 1) : null;
        $averageColor = $this->getColorForBasicPercentage($averagePercentage);
        $averageDisplay = $averagePercentage !== null ? $averagePercentage . '%' : '-';

        return array_merge($assignment, [
            'average_score' => null,
            'average_percentage' => $averagePercentage,
            'graded_count' => $gradedCount,
            'total_students' => $totalStudents,
            'good_count' => $goodSufficientCount,
            'average_display' => $averageDisplay,
            'average_color' => $averageColor
        ]);
    }

    /**
     * Voor PUNTEN rapporten: bereken punten gemiddelde per opdracht
     */
    protected function calculatePointBasedAssignmentAverage(array $assignment, Collection $studentsProgress, string $assignmentName): array
    {
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
    }

    /**
     * Bereken klas gemiddelde - AANGEPAST
     */
    public function calculateClassAverage(Collection $studentsProgress, string $reportType = 'basic'): array
    {
        $studentsWithAverages = $this->calculateStudentAverages($studentsProgress, $reportType);

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
        $colorMethod = $reportType === 'basic' ? 'getColorForBasicPercentage' : 'getColorForPercentage';

        return [
            'class_average' => $classAverage,
            'class_average_display' => $classAverage . '%',
            'class_average_color' => $this->$colorMethod($classAverage),
            'students_with_grades' => $studentsWithScores->count(),
            'total_students' => $studentsProgress->count()
        ];
    }

    /**
     * Bepaal kleur voor BASIC percentage (goed/voldoende ratio)
     */
    protected function getColorForBasicPercentage($percentage): string
    {
        if ($percentage === null) {
            return 'bg-gray-200 text-gray-600';
        }

        if ($percentage >= 75) {
            return 'bg-green-200 text-green-800';
        } elseif ($percentage >= 55) {
            return 'bg-yellow-200 text-yellow-800';
        } else {
            return 'bg-red-200 text-red-800';
        }
    }

    /**
     * Bepaal kleur voor punten-gebaseerde percentages
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
