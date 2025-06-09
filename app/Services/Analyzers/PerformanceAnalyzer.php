<?php

namespace App\Services\Analyzers;

use Illuminate\Support\Collection;

class PerformanceAnalyzer
{
    public function calculateAttentionRisks(Collection $studentsProgress): Collection
    {
        return $studentsProgress->map(function ($student) {
            $riskData = $this->calculateStudentRisk($student);

            if ($riskData['risk_score'] > 0) {
                return array_merge($student, $riskData);
            }

            return null;
        })->filter()->sortByDesc('risk_score');
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
}
