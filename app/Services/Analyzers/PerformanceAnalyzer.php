<?php

namespace App\Services\Analyzers;

use Illuminate\Support\Collection;

class PerformanceAnalyzer
{
    public function calculateAttentionRisks(Collection $studentsProgress): Collection
    {
        $studentsWithRisk = collect();

        foreach ($studentsProgress as $student) {
            $riskAnalysis = $this->analyzeStudentRisk($student);

            if ($riskAnalysis['risk_score'] > 0) {
                $studentsWithRisk->push(array_merge($riskAnalysis, [
                    'student' => $student
                ]));
            }
        }

        // Sorteer op risico score (hoogste eerst)
        return $studentsWithRisk->sortByDesc('risk_score')->values();
    }

    protected function calculateStudentRisk(array $student): array
    {
        $assignmentsByGroup = $this->groupAssignmentsByGroup($student['assignments']);

        $missing = 0;
        $insufficient = 0;
        $needsGrading = 0;
        $needsReview = 0;
        $late = 0;

        $missingList = [];
        $insufficientList = [];
        $needsGradingList = [];
        $lateList = [];

        $insufficientRiskScore = 0;

        foreach ($assignmentsByGroup as $groupName => $assignments) {
            $hasSubmissions = $this->groupHasSubmissions($assignments);

            foreach ($assignments as $assignment) {
                if ($assignment['excused']) continue;

                $riskCounts = $this->analyzeAssignmentRisk($assignment, $hasSubmissions);

                $missing += $riskCounts['missing'];
                $insufficient += $riskCounts['insufficient'];
                $needsGrading += $riskCounts['needs_grading'];
                $needsReview += $riskCounts['needs_review'];
                $late += $riskCounts['late'];
                $insufficientRiskScore += $riskCounts['insufficient_risk_score'];

                $this->addToLists($assignment, $riskCounts, $missingList, $insufficientList, $needsGradingList, $lateList);
            }
        }

        $totalRiskScore = ($missing * 3) + $insufficientRiskScore + $late + $needsGrading + $needsReview;
        $riskLevel = $this->determineRiskLevel($totalRiskScore);

        return [
            'risk_score' => $totalRiskScore,
            'risk_level' => $riskLevel,
            'missing' => $missing,
            'insufficient' => $insufficient,
            'needs_grading' => $needsGrading,
            'needs_review' => $needsReview,
            'late' => $late,
            'missing_list' => $missingList,
            'insufficient_list' => $insufficientList,
            'needs_grading_list' => $needsGradingList,
            'late_list' => $lateList,
        ];
    }

    protected function groupAssignmentsByGroup(Collection $assignments): array
    {
        return $assignments->groupBy('assignment_group_name')->toArray();
    }

    protected function groupHasSubmissions(array $assignments): bool
    {
        return collect($assignments)->contains(function ($assignment) {
            return in_array($assignment['status'], ['submitted', 'graded']);
        });
    }

    protected function analyzeAssignmentRisk(array $assignment, bool $hasSubmissions): array
    {
        $isNonSubmittable = empty($assignment['submission_types']) || in_array('none', $assignment['submission_types']);
        $hasGrade = $assignment['status'] === 'graded' && $assignment['score'] !== null;

        $risk = [
            'missing' => 0,
            'insufficient' => 0,
            'needs_grading' => 0,
            'needs_review' => 0,
            'late' => 0,
            'insufficient_risk_score' => 0,
        ];

        // Missing assignments
        if (!$isNonSubmittable && $assignment['status'] === 'unsubmitted') {
            $risk['missing'] = 1;
        }

        // Insufficient grades with smart weighting
        if ($hasGrade && $assignment['points_possible'] > 0) {
            $percentage = ($assignment['score'] / $assignment['points_possible']) * 100;
            if ($percentage < 55) {
                $risk['insufficient'] = 1;
                $risk['insufficient_risk_score'] = ($isNonSubmittable && $hasSubmissions) ? 1 : 3;
            }
        }

        // Needs grading
        if ($isNonSubmittable && !$hasGrade && $assignment['points_possible'] > 0) {
            $risk['needs_grading'] = 1;
        }

        // Needs review
        if ($assignment['status'] === 'submitted') {
            $risk['needs_review'] = 1;
        }

        // Late submissions (only problematic ones)
        if ($this->isLateAndProblematic($assignment)) {
            $risk['late'] = 1;
        }

        return $risk;
    }

    protected function isLateAndProblematic(array $assignment): bool
    {
        if (!isset($assignment['submitted_at'], $assignment['due_at'])) {
            return false;
        }

        $isLate = strtotime($assignment['submitted_at']) > strtotime($assignment['due_at']);
        if (!$isLate) return false;

        // Check if still problematic
        if ($assignment['status'] === 'submitted') return true;

        if ($assignment['status'] === 'graded' && $assignment['points_possible'] > 0) {
            $percentage = ($assignment['score'] / $assignment['points_possible']) * 100;
            return $percentage < 55;
        }

        return false;
    }

    protected function addToLists(array $assignment, array $riskCounts, array &$missingList, array &$insufficientList, array &$needsGradingList, array &$lateList): void
    {
        if ($riskCounts['missing'] > 0) {
            $missingList[] = $assignment['assignment_name'];
        }

        if ($riskCounts['insufficient'] > 0) {
            $percentage = round(($assignment['score'] / $assignment['points_possible']) * 100);
            $insufficientList[] = $assignment['assignment_name'] . " ({$percentage}%)";
        }

        if ($riskCounts['needs_grading'] > 0) {
            $needsGradingList[] = $assignment['assignment_name'];
        }

        if ($riskCounts['late'] > 0) {
            $lateList[] = $assignment['assignment_name'];
        }
    }

    protected function determineRiskLevel(int $riskScore): string
    {
        return match (true) {
            $riskScore >= 5 => 'urgent',
            $riskScore >= 3 => 'high',
            $riskScore >= 1 => 'medium',
            default => 'none'
        };
    }

    protected function analyzeStudentRisk(array $student): array
    {
        $missing = 0;
        $insufficient = 0;
        $late = 0;
        $needsReview = 0;
        $needsGrading = 0;

        $missingList = [];
        $insufficientList = [];
        $lateList = [];
        $needsGradingList = [];

        // Bepaal of er inleverbare opdrachten zijn in elke assignment group
        $assignmentGroupHasSubmissions = $this->getAssignmentGroupSubmissionInfo($student);

        foreach ($student['assignments'] as $assignment) {
            $assignmentName = $assignment['assignment_name'];
            $status = $assignment['status'] ?? 'unknown';
            $score = $assignment['score'] ?? null;
            $pointsPossible = $assignment['points_possible'] ?? 0;
            $isLate = $assignment['late'] ?? false;
            $moduleName = $assignment['module_name'] ?? 'Onbekend';
            $groupName = $assignment['assignment_group_name'] ?? 'Onbekend';

            // Bepaal of deze assignment group inleveringen heeft
            $groupHasSubmissions = $assignmentGroupHasSubmissions[$groupName] ?? false;

            switch ($status) {
                case 'missing':
                case 'unsubmitted':
                    $missing++;
                    $missingList[] = $assignmentName;
                    break;

                case 'graded':
                    if ($pointsPossible > 0) {
                        $percentage = ($score / $pointsPossible) * 100;
                        if ($percentage < 55) {
                            $insufficient++;
                            $insufficientList[] = $assignmentName;
                        }
                    }

                    if ($isLate) {
                        $late++;
                        $lateList[] = $assignmentName;
                    }
                    break;

                case 'submitted':
                    $needsReview++;
                    if ($isLate) {
                        $late++;
                        $lateList[] = $assignmentName;
                    }
                    break;

                case 'pending_review':
                    $needsReview++;
                    break;

                default:
                    // Voor opdrachten zonder status die wel punten kunnen hebben
                    if ($pointsPossible > 0) {
                        $needsGrading++;
                        $needsGradingList[] = $assignmentName;
                    }
                    break;
            }
        }

        // Bereken risico score met slimme weging
        $riskScore = $this->calculateRiskScore($missing, $insufficient, $late, $needsReview, $needsGrading, $assignmentGroupHasSubmissions, $student);

        // Bepaal risico niveau
        $riskLevel = 'low';
        if ($riskScore >= 5) {
            $riskLevel = 'urgent';
        } elseif ($riskScore >= 3) {
            $riskLevel = 'high';
        } elseif ($riskScore >= 1) {
            $riskLevel = 'medium';
        }

        return [
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'missing' => $missing,
            'insufficient' => $insufficient,
            'late' => $late,
            'needs_review' => $needsReview,
            'needs_grading' => $needsGrading,
            'missing_list' => $missingList,
            'insufficient_list' => $insufficientList,
            'late_list' => $lateList,
            'needs_grading_list' => $needsGradingList
        ];
    }

    protected function getAssignmentGroupSubmissionInfo(array $student): array
    {
        $groupSubmissionInfo = [];

        foreach ($student['assignments'] as $assignment) {
            $groupName = $assignment['assignment_group_name'] ?? 'Onbekend';
            $status = $assignment['status'] ?? 'unknown';

            if (!isset($groupSubmissionInfo[$groupName])) {
                $groupSubmissionInfo[$groupName] = false;
            }

            // Als er minimaal één inlevering is in deze groep
            if (in_array($status, ['submitted', 'graded', 'pending_review'])) {
                $groupSubmissionInfo[$groupName] = true;
            }
        }

        return $groupSubmissionInfo;
    }

    protected function calculateRiskScore(int $missing, int $insufficient, int $late, int $needsReview, int $needsGrading, array $assignmentGroupHasSubmissions, array $student): int
    {
        $score = 0;

        // Ontbrekende opdrachten zijn altijd zwaar (3 punten)
        $score += $missing * 3;

        // Onvoldoende opdrachten: slimme weging
        foreach ($student['assignments'] as $assignment) {
            $status = $assignment['status'] ?? 'unknown';
            $score_val = $assignment['score'] ?? null;
            $pointsPossible = $assignment['points_possible'] ?? 0;
            $groupName = $assignment['assignment_group_name'] ?? 'Onbekend';

            if ($status === 'graded' && $pointsPossible > 0) {
                $percentage = ($score_val / $pointsPossible) * 100;
                if ($percentage < 55) {
                    // Als de assignment group inleveringen heeft, is onvoldoende minder erg (1 punt)
                    // Anders zwaarder wegen (3 punten)
                    $groupHasSubmissions = $assignmentGroupHasSubmissions[$groupName] ?? false;
                    $score += $groupHasSubmissions ? 1 : 3;
                }
            }
        }

        // Te laat ingeleverd (1 punt)
        $score += $late * 1;

        // Nog te beoordelen (1 punt)
        $score += $needsReview * 1;

        // Nog geen cijfer (1 punt)
        $score += $needsGrading * 1;

        return $score;
    }
}
