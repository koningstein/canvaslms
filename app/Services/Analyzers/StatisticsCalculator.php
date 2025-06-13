<?php

namespace App\Services\Analyzers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StatisticsCalculator
{
    public function __construct(
        protected AverageCalculator $averageCalculator
    ) {}

    public function calculateBasicStats(Collection $studentsProgress): array
    {
        $totalStudents = $studentsProgress->count();

        // Tel alleen opdrachten met punten voor consistentie
        $totalAssignments = 0;
        if ($studentsProgress->isNotEmpty() && isset($studentsProgress->first()['assignments'])) {
            $totalAssignments = $this->averageCalculator->countAssessableAssignments($studentsProgress->first()['assignments']);
        }

        // Tel submissions (graded + submitted)
        $totalSubmissions = $studentsProgress->sum(function($student) {
            return $student['assignments']->whereIn('status', ['graded', 'submitted', 'good', 'sufficient', 'insufficient'])->count();
        });

        // Tel alleen echt beoordeelde opdrachten met punten
        $totalGradedAssignments = $studentsProgress->sum(function($student) {
            return $this->averageCalculator->countGradedAssignments($student['assignments']);
        });

        // Bereken totaal punten
        $totalPointsAwarded = $studentsProgress->sum(function($student) {
            return $student['assignments']->filter(function($assignment) {
                $status = $assignment['status'] ?? '';
                return in_array($status, ['graded', 'good', 'sufficient', 'insufficient']) &&
                    isset($assignment['score']) &&
                    is_numeric($assignment['score']);
            })->sum('score');
        });

        $totalPointsPossible = $studentsProgress->sum(function($student) {
            return $student['assignments']->filter(function($assignment) {
                return isset($assignment['points_possible']) &&
                    $assignment['points_possible'] > 0;
            })->sum('points_possible');
        });

        // Bereken completion rate - percentage van opdrachten met punten die beoordeeld zijn
        $totalAssignmentsWithPoints = $studentsProgress->sum(function($student) {
            return $this->averageCalculator->countAssessableAssignments($student['assignments']);
        });

        $completionRate = $totalAssignmentsWithPoints > 0 ?
            round(($totalGradedAssignments / $totalAssignmentsWithPoints) * 100, 1) : 0;

        // Gebruik AverageCalculator voor klas gemiddelde
        $classAverageData = [];
        $averagePercentage = 0;

        if ($studentsProgress->isNotEmpty()) {
            $classAverageData = $this->averageCalculator->calculateClassAverage($studentsProgress);
            $averagePercentage = $classAverageData['class_average'] ?? 0;
        }

        return [
            // Snake case (nieuw/preferred)
            'total_students' => $totalStudents,
            'total_assignments' => $totalAssignments,
            'total_submissions' => $totalSubmissions,
            'total_graded_assignments' => $totalGradedAssignments,
            'total_points_awarded' => round($totalPointsAwarded, 1),
            'total_points_possible' => round($totalPointsPossible, 1),
            'completion_rate' => $completionRate,
            'average_percentage' => $averagePercentage,

            // Camel case (voor bestaande views)
            'totalStudents' => $totalStudents,
            'totalAssignments' => $totalAssignments,
            'totalSubmissions' => $totalSubmissions,
            'totalGradedAssignments' => $totalGradedAssignments,
            'totalPointsAwarded' => round($totalPointsAwarded, 1),
            'totalPointsPossible' => round($totalPointsPossible, 1),
            'completionRate' => $completionRate,
            'averagePercentage' => $averagePercentage,
        ];
    }

    public function calculateGradeDistribution(Collection $studentsProgress): array
    {
        $distribution = [
            'excellent' => 0,  // ≥90%
            'good' => 0,       // 75-89%
            'sufficient' => 0, // 55-74%
            'insufficient' => 0, // <55%
            'not_graded' => 0,
        ];

        foreach ($studentsProgress as $student) {
            foreach ($student['assignments'] as $assignment) {
                $status = $assignment['status'] ?? '';

                if (in_array($status, ['graded', 'good', 'sufficient', 'insufficient']) &&
                    isset($assignment['score']) &&
                    is_numeric($assignment['score']) &&
                    isset($assignment['points_possible']) &&
                    $assignment['points_possible'] > 0) {

                    $percentage = ($assignment['score'] / $assignment['points_possible']) * 100;

                    if ($percentage >= 90) {
                        $distribution['excellent']++;
                    } elseif ($percentage >= 75) {
                        $distribution['good']++;
                    } elseif ($percentage >= 55) {
                        $distribution['sufficient']++;
                    } else {
                        $distribution['insufficient']++;
                    }
                } else {
                    $distribution['not_graded']++;
                }
            }
        }

        return $distribution;
    }

    public function calculateAssignmentStatistics(Collection $studentsProgress): Collection
    {
        if ($studentsProgress->isEmpty()) {
            Log::warning('No students progress data for assignment statistics');
            return collect();
        }

        $firstStudent = $studentsProgress->first();
        if (!isset($firstStudent['assignments']) || $firstStudent['assignments']->isEmpty()) {
            Log::warning('No assignments found in first student data');
            return collect();
        }

        $allAssignments = $firstStudent['assignments'];
        Log::info('Processing assignment statistics', [
            'total_assignments' => $allAssignments->count(),
            'total_students' => $studentsProgress->count()
        ]);

        return $allAssignments->map(function ($assignment) use ($studentsProgress) {
            $assignmentName = $assignment['assignment_name'];

            // Verzamel alle student responses voor deze opdracht
            $responses = $studentsProgress->map(function ($student) use ($assignmentName) {
                if (!isset($student['assignments'])) {
                    return null;
                }
                return $student['assignments']->where('assignment_name', $assignmentName)->first();
            })->filter();

            $totalResponses = $responses->count();

            // Filter alleen echt beoordeelde responses
            $gradedResponses = $responses->filter(function($response) {
                $status = $response['status'] ?? '';
                return in_array($status, ['graded', 'good', 'sufficient', 'insufficient']) &&
                    isset($response['score']) &&
                    is_numeric($response['score']) &&
                    isset($response['points_possible']) &&
                    $response['points_possible'] > 0; // Alleen opdrachten met punten
            });

            $submittedResponses = $responses->whereIn('status', ['graded', 'submitted', 'good', 'sufficient', 'insufficient']);

            // Bereken gemiddelde als er scores zijn
            $averageScore = null;
            $averagePercentage = null;

            if ($gradedResponses->isNotEmpty()) {
                $averageScore = round($gradedResponses->avg('score'), 1);
                $totalScore = $gradedResponses->sum('score');
                $totalPossible = $gradedResponses->sum('points_possible');
                $averagePercentage = $totalPossible > 0 ? round(($totalScore / $totalPossible) * 100, 1) : 0;
            }

            // Bepaal kleur en status voor opdracht
            $averageColor = $this->getAssignmentStatusColor($averagePercentage, $gradedResponses->count());
            $statusText = $this->getAssignmentStatusText($averagePercentage, $gradedResponses->count(), $totalResponses);

            Log::debug('Assignment statistics calculated', [
                'assignment' => $assignmentName,
                'graded_count' => $gradedResponses->count(),
                'average_percentage' => $averagePercentage,
                'has_points' => isset($assignment['points_possible']) && $assignment['points_possible'] > 0
            ]);

            return array_merge($assignment, [
                'total_responses' => $totalResponses,
                'graded_count' => $gradedResponses->count(),
                'submitted_count' => $submittedResponses->count(),
                'average_score' => $averageScore,
                'average_percentage' => $averagePercentage,
                'completion_percentage' => $totalResponses > 0 ? round(($gradedResponses->count() / $totalResponses) * 100, 1) : 0,
                'display_value' => $averagePercentage !== null ? $averagePercentage . '%' : '-',
                'average_color' => $averageColor,
                'status_text' => $statusText,
                'status_color' => $this->getStatusColor($gradedResponses->count(), $totalResponses),
                'difficulty_text' => $this->getDifficultyText($averagePercentage, $gradedResponses->count()),
                'difficulty_color' => $this->getDifficultyColor($averagePercentage, $gradedResponses->count()),
            ]);
        })->filter(function($assignment) {
            // Optioneel: filter opdrachten zonder punten uit voor chart data
            return isset($assignment['points_possible']) && $assignment['points_possible'] > 0;
        });
    }

    public function calculateMissingStats(Collection $studentsProgress): array
    {
        $totalStudents = $studentsProgress->count();

        $totalMissing = $studentsProgress->sum(function($student) {
            return $student['assignments']->where('status', 'unsubmitted')->count();
        });

        $totalInsufficient = $studentsProgress->sum(function($student) {
            return $student['assignments']->filter(function($assignment) {
                $status = $assignment['status'] ?? '';
                return in_array($status, ['graded', 'good', 'sufficient', 'insufficient']) &&
                    isset($assignment['score']) &&
                    isset($assignment['points_possible']) &&
                    $assignment['points_possible'] > 0 &&
                    (($assignment['score'] / $assignment['points_possible']) * 100) < 55;
            })->count();
        });

        $totalProblematic = $totalMissing + $totalInsufficient;

        $studentsWithProblemsCount = $studentsProgress->filter(function($student) {
            $missing = $student['assignments']->where('status', 'unsubmitted')->count();
            $insufficient = $student['assignments']->filter(function($assignment) {
                $status = $assignment['status'] ?? '';
                return in_array($status, ['graded', 'good', 'sufficient', 'insufficient']) &&
                    isset($assignment['score']) &&
                    isset($assignment['points_possible']) &&
                    $assignment['points_possible'] > 0 &&
                    (($assignment['score'] / $assignment['points_possible']) * 100) < 55;
            })->count();

            return ($missing + $insufficient) > 0;
        })->count();

        $problemRate = $totalStudents > 0 ? round(($studentsWithProblemsCount / $totalStudents) * 100, 1) : 0;

        return [
            'totalMissing' => $totalMissing,
            'totalInsufficient' => $totalInsufficient,
            'totalProblematic' => $totalProblematic,
            'studentsWithProblemsCount' => $studentsWithProblemsCount,
            'problemRate' => $problemRate,
            'totalStudents' => $totalStudents,
        ];
    }

    // Helper methods voor assignment statistics
    protected function getAssignmentStatusColor(?float $percentage, int $gradedCount): string
    {
        if ($percentage === null || $gradedCount === 0) {
            return 'bg-gray-100 text-gray-600';
        }

        if ($percentage >= 75) {
            return 'bg-green-100 text-green-800';
        } elseif ($percentage >= 55) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }

    protected function getAssignmentStatusText(?float $percentage, int $gradedCount, int $totalResponses): string
    {
        if ($gradedCount === 0) {
            return 'Niet beoordeeld';
        }

        $completionRate = round(($gradedCount / $totalResponses) * 100);

        if ($completionRate < 50) {
            return 'Gedeeltelijk';
        } elseif ($completionRate < 100) {
            return 'Bijna klaar';
        } else {
            return 'Compleet';
        }
    }

    protected function getStatusColor(int $gradedCount, int $totalResponses): string
    {
        if ($gradedCount === 0) {
            return 'bg-gray-100 text-gray-600';
        }

        $completionRate = ($gradedCount / $totalResponses) * 100;

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
