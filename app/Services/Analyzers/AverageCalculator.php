<?php

namespace App\Services\Analyzers;

use Illuminate\Support\Collection;

class AverageCalculator
{
    /**
     * Bereken gemiddelde scores per student - UNIFORME AANPAK
     */
    public function calculateStudentAverages(Collection $studentsProgress, string $reportType = 'basic'): Collection
    {
        if ($studentsProgress->isEmpty()) {
            return collect();
        }

        return $studentsProgress->map(function ($student) use ($reportType) {
            $assignments = $student['assignments'] ?? collect();

            // Gebruik altijd punten-gebaseerde berekening voor consistentie
            return $this->calculateStudentAverage($student, $assignments, $reportType);
        });
    }

    /**
     * UNIFORME student gemiddelde berekening
     */
    protected function calculateStudentAverage(array $student, Collection $assignments, string $reportType): array
    {
        // Filter alleen opdrachten met punten voor consistente berekening
        $assignmentsWithPoints = $assignments->filter(function ($assignment) {
            return isset($assignment['points_possible']) && $assignment['points_possible'] > 0;
        });

        // Filter alleen echt beoordeelde opdrachten
        $gradedAssignments = $assignmentsWithPoints->filter(function ($assignment) {
            $status = $assignment['status'] ?? '';
            return in_array($status, ['graded', 'good', 'sufficient', 'insufficient']) &&
                isset($assignment['score']) &&
                is_numeric($assignment['score']);
        });

        if ($gradedAssignments->isEmpty()) {
            return array_merge($student, [
                'average_score' => null,
                'average_percentage' => null,
                'graded_count' => 0,
                'total_assignments' => $assignmentsWithPoints->count(),
                'average_display' => 'Geen cijfers',
                'average_color' => 'bg-gray-200 text-gray-600'
            ]);
        }

        // Bereken totaal behaalde punten en totaal mogelijke punten
        $totalScore = $gradedAssignments->sum('score');
        $totalPossible = $gradedAssignments->sum('points_possible');

        $averagePercentage = $totalPossible > 0 ? round(($totalScore / $totalPossible) * 100, 1) : 0;
        $averageScore = round($totalScore, 1);

        // Bepaal display en kleur op basis van report type
        $displayData = $this->getStudentDisplayData($averagePercentage, $averageScore, $reportType);

        return array_merge($student, [
            'average_score' => $averageScore,
            'average_percentage' => $averagePercentage,
            'graded_count' => $gradedAssignments->count(),
            'total_assignments' => $assignmentsWithPoints->count(),
            'average_display' => $displayData['display'],
            'average_color' => $displayData['color']
        ]);
    }

    /**
     * Bereken gemiddelde scores per opdracht - UNIFORME AANPAK
     */
    public function calculateAssignmentAverages(Collection $studentsProgress, string $reportType = 'basic'): Collection
    {
        if ($studentsProgress->isEmpty()) {
            return collect();
        }

        // Verzamel alle unieke opdrachten
        $firstStudent = $studentsProgress->first();
        if (!isset($firstStudent['assignments'])) {
            return collect();
        }

        $allAssignments = $firstStudent['assignments'];

        return $allAssignments->map(function ($assignment) use ($studentsProgress, $reportType) {
            $assignmentName = $assignment['assignment_name'];

            return $this->calculateSingleAssignmentAverage($assignment, $studentsProgress, $assignmentName, $reportType);
        });
    }

    /**
     * UNIFORME opdracht gemiddelde berekening
     */
    protected function calculateSingleAssignmentAverage(array $assignment, Collection $studentsProgress, string $assignmentName, string $reportType): array
    {
        // Check of opdracht punten heeft
        $hasPoints = isset($assignment['points_possible']) && $assignment['points_possible'] > 0;

        if (!$hasPoints) {
            return array_merge($assignment, [
                'average_score' => null,
                'average_percentage' => null,
                'graded_count' => 0,
                'total_students' => $studentsProgress->count(),
                'average_display' => '-',
                'average_color' => 'bg-gray-300'
            ]);
        }

        $totalStudents = $studentsProgress->count();
        $totalScore = 0;
        $totalPossible = 0;
        $gradedCount = 0;

        // Verzamel scores van alle studenten voor deze opdracht
        foreach ($studentsProgress as $student) {
            $assignments = $student['assignments'] ?? collect();
            $response = $assignments->where('assignment_name', $assignmentName)->first();

            if ($response &&
                isset($response['score']) &&
                is_numeric($response['score']) &&
                isset($response['points_possible']) &&
                $response['points_possible'] > 0) {

                $status = $response['status'] ?? '';
                if (in_array($status, ['graded', 'good', 'sufficient', 'insufficient'])) {
                    $totalScore += $response['score'];
                    $totalPossible += $response['points_possible'];
                    $gradedCount++;
                }
            }
        }

        if ($gradedCount === 0) {
            return array_merge($assignment, [
                'average_score' => null,
                'average_percentage' => null,
                'graded_count' => 0,
                'total_students' => $totalStudents,
                'average_display' => '-',
                'average_color' => 'bg-gray-200'
            ]);
        }

        // Bereken gemiddelde
        $averageScore = round($totalScore / $gradedCount, 1);
        $averagePercentage = $totalPossible > 0 ? round(($totalScore / $totalPossible) * 100, 1) : 0;

        // Bepaal display en kleur
        $displayData = $this->getAssignmentDisplayData($averagePercentage, $averageScore, $reportType);

        return array_merge($assignment, [
            'average_score' => $averageScore,
            'average_percentage' => $averagePercentage,
            'graded_count' => $gradedCount,
            'total_students' => $totalStudents,
            'average_display' => $displayData['display'],
            'average_color' => $displayData['color'],
            // Voeg extra velden toe voor view compatibiliteit
            'display_value' => $displayData['display'],
            'completion_percentage' => $totalStudents > 0 ? round(($gradedCount / $totalStudents) * 100, 1) : 0,
            'status_text' => $this->getAssignmentStatusText($gradedCount, $totalStudents),
            'status_color' => $this->getAssignmentStatusColor($gradedCount, $totalStudents),
            'difficulty_text' => $this->getDifficultyText($averagePercentage, $gradedCount),
            'difficulty_color' => $this->getDifficultyColor($averagePercentage, $gradedCount),
        ]);
    }

    /**
     * Bereken klas gemiddelde - UNIFORME AANPAK
     */
    public function calculateClassAverage(Collection $studentsProgress, string $reportType = 'basic'): array
    {
        $studentsWithAverages = $this->calculateStudentAverages($studentsProgress, $reportType);

        $studentsWithScores = $studentsWithAverages->filter(function ($student) {
            return $student['average_percentage'] !== null && $student['average_percentage'] > 0;
        });

        if ($studentsWithScores->isEmpty()) {
            return [
                'class_average' => null,
                'class_average_display' => 'Geen cijfers',
                'class_average_color' => 'bg-gray-200 text-gray-600',
                'students_with_grades' => 0,
                'total_students' => $studentsProgress->count()
            ];
        }

        $classAverage = round($studentsWithScores->avg('average_percentage'), 1);
        $displayData = $this->getClassDisplayData($classAverage, $reportType);

        return [
            'class_average' => $classAverage,
            'class_average_display' => $displayData['display'],
            'class_average_color' => $displayData['color'],
            'students_with_grades' => $studentsWithScores->count(),
            'total_students' => $studentsProgress->count()
        ];
    }

    /**
     * Bepaal display en kleur voor student gemiddelde
     */
    protected function getStudentDisplayData(float $percentage, float $score, string $reportType): array
    {
        $color = $this->getColorForPercentage($percentage);

        $display = match($reportType) {
            'grades' => number_format($score, 1),
            'percentages' => $percentage . '%',
            'basic' => $percentage . '%',
            default => $percentage . '%'
        };

        return ['display' => $display, 'color' => $color];
    }

    /**
     * Bepaal display en kleur voor opdracht gemiddelde
     */
    protected function getAssignmentDisplayData(float $percentage, float $score, string $reportType): array
    {
        $color = $this->getColorForPercentage($percentage);

        $display = match($reportType) {
            'grades' => number_format($score, 1),
            'percentages' => $percentage . '%',
            'basic' => $percentage . '%',
            default => $percentage . '%'
        };

        return ['display' => $display, 'color' => $color];
    }

    /**
     * Bepaal display en kleur voor klas gemiddelde
     */
    protected function getClassDisplayData(float $percentage, string $reportType): array
    {
        $color = $this->getColorForPercentage($percentage);

        $display = match($reportType) {
            'grades' => $percentage . '%', // Ook voor grades: toon percentage
            'percentages' => $percentage . '%',
            'basic' => $percentage . '%',
            default => $percentage . '%'
        };

        return ['display' => $display, 'color' => $color];
    }

    /**
     * CONSISTENTE kleur bepaling voor alle percentages
     */
    protected function getColorForPercentage(float $percentage): string
    {
        if ($percentage >= 75) {
            return 'bg-green-200 text-green-800';
        } elseif ($percentage >= 55) {
            return 'bg-yellow-200 text-yellow-800';
        } else {
            return 'bg-red-200 text-red-800';
        }
    }

    /**
     * HELPER: Tel beoordeelbare opdrachten voor een student
     */
    public function countAssessableAssignments(Collection $assignments): int
    {
        if ($assignments->isEmpty()) {
            return 0;
        }

        return $assignments->filter(function ($assignment) {
            return isset($assignment['points_possible']) && $assignment['points_possible'] > 0;
        })->count();
    }

    /**
     * HELPER: Tel beoordeelde opdrachten voor een student
     */
    public function countGradedAssignments(Collection $assignments): int
    {
        if ($assignments->isEmpty()) {
            return 0;
        }

        return $assignments->filter(function ($assignment) {
            $status = $assignment['status'] ?? '';
            return in_array($status, ['graded', 'good', 'sufficient', 'insufficient']) &&
                isset($assignment['score']) &&
                is_numeric($assignment['score']) &&
                isset($assignment['points_possible']) &&
                $assignment['points_possible'] > 0;
        })->count();
    }

    // Helper methods voor view compatibiliteit
    protected function getAssignmentStatusText(int $gradedCount, int $totalStudents): string
    {
        if ($gradedCount === 0) {
            return 'Niet beoordeeld';
        }

        $completionRate = round(($gradedCount / $totalStudents) * 100);

        if ($completionRate < 50) {
            return 'Gedeeltelijk';
        } elseif ($completionRate < 100) {
            return 'Bijna klaar';
        } else {
            return 'Compleet';
        }
    }

    protected function getAssignmentStatusColor(int $gradedCount, int $totalStudents): string
    {
        if ($gradedCount === 0) {
            return 'bg-gray-100 text-gray-600';
        }

        $completionRate = ($gradedCount / $totalStudents) * 100;

        if ($completionRate < 50) {
            return 'bg-red-100 text-red-800';
        } elseif ($completionRate < 100) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-green-100 text-green-800';
        }
    }

    protected function getDifficultyText(?float $percentage, int $gradedCount): string
    {
        if ($percentage === null || $gradedCount < 3) {
            return 'Onbekend';
        }

        return match (true) {
            $percentage >= 80 => 'Makkelijk',
            $percentage >= 65 => 'Gemiddeld',
            $percentage >= 50 => 'Moeilijk',
            default => 'Zeer moeilijk'
        };
    }

    protected function getDifficultyColor(?float $percentage, int $gradedCount): string
    {
        if ($percentage === null || $gradedCount < 3) {
            return 'bg-gray-100 text-gray-600';
        }

        return match (true) {
            $percentage >= 80 => 'bg-green-100 text-green-800',
            $percentage >= 65 => 'bg-blue-100 text-blue-800',
            $percentage >= 50 => 'bg-orange-100 text-orange-800',
            default => 'bg-red-100 text-red-800'
        };
    }
}
