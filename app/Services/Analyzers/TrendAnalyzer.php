<?php

namespace App\Services\Analyzers;

use Illuminate\Support\Collection;

class TrendAnalyzer
{
    public function calculateTrendData(Collection $studentsProgress): array
    {
        $submissionsByDate = $this->groupSubmissionsByDate($studentsProgress);
        $dailyAverages = $this->calculateDailyAverages($submissionsByDate);

        return [
            'dates' => array_keys($dailyAverages),
            'values' => array_values($dailyAverages),
            'total_submissions' => array_sum(array_map('count', $submissionsByDate)),
            'date_range' => $this->getDateRange($submissionsByDate),
        ];
    }

    public function analyzeSubmissionPatterns(Collection $studentsProgress): array
    {
        $patterns = [
            'early_submissions' => 0,
            'on_time_submissions' => 0,
            'late_submissions' => 0,
            'very_late_submissions' => 0,
        ];

        foreach ($studentsProgress as $student) {
            foreach ($student['assignments'] as $assignment) {
                if (!isset($assignment['submitted_at'], $assignment['due_at'])) {
                    continue;
                }

                $daysDifference = $this->getDaysDifference($assignment['submitted_at'], $assignment['due_at']);

                if ($daysDifference < -1) {
                    $patterns['early_submissions']++;
                } elseif ($daysDifference <= 0) {
                    $patterns['on_time_submissions']++;
                } elseif ($daysDifference <= 7) {
                    $patterns['late_submissions']++;
                } else {
                    $patterns['very_late_submissions']++;
                }
            }
        }

        return $patterns;
    }

    protected function groupSubmissionsByDate(Collection $studentsProgress): array
    {
        $submissionsByDate = [];

        foreach ($studentsProgress as $student) {
            foreach ($student['assignments'] as $assignment) {
                if ($assignment['status'] !== 'graded' ||
                    !isset($assignment['submitted_at'], $assignment['score'], $assignment['points_possible']) ||
                    $assignment['points_possible'] <= 0) {
                    continue;
                }

                $date = date('Y-m-d', strtotime($assignment['submitted_at']));
                $percentage = ($assignment['score'] / $assignment['points_possible']) * 100;

                if (!isset($submissionsByDate[$date])) {
                    $submissionsByDate[$date] = [];
                }

                $submissionsByDate[$date][] = $percentage;
            }
        }

        return $submissionsByDate;
    }

    protected function calculateDailyAverages(array $submissionsByDate): array
    {
        $dailyAverages = [];

        foreach ($submissionsByDate as $date => $percentages) {
            // Only include dates with multiple submissions for reliability
            if (count($percentages) >= 2) {
                $average = round(array_sum($percentages) / count($percentages), 1);
                $formattedDate = date('d-m', strtotime($date));
                $dailyAverages[$formattedDate] = $average;
            }
        }

        // Sort by original date
        uksort($dailyAverages, function ($a, $b) {
            return strtotime("2024-{$a}") <=> strtotime("2024-{$b}");
        });

        return $dailyAverages;
    }

    protected function getDaysDifference(string $submittedAt, string $dueAt): int
    {
        $submitted = strtotime($submittedAt);
        $due = strtotime($dueAt);

        return (int) ceil(($submitted - $due) / (24 * 60 * 60));
    }

    protected function getDateRange(array $submissionsByDate): array
    {
        if (empty($submissionsByDate)) {
            return ['start' => null, 'end' => null];
        }

        $dates = array_keys($submissionsByDate);
        sort($dates);

        return [
            'start' => reset($dates),
            'end' => end($dates),
        ];
    }
}
