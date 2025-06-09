<?php

namespace App\DTOs;

use Illuminate\Support\Collection;

class ProcessedReportData
{
    public function __construct(
        public readonly string $reportType,
        public readonly Collection $studentsProgress,
        public readonly array $statistics = [],
        public readonly array $chartData = [],
        public readonly array $insights = []
    ) {}

    public function getTotalStudents(): int
    {
        return $this->studentsProgress->count();
    }

    public function getTotalAssignments(): int
    {
        return $this->studentsProgress->isNotEmpty() ?
            $this->studentsProgress->first()['assignments']->count() : 0;
    }

    public function getStudentsWithProblems(): Collection
    {
        return $this->studentsProgress->filter(function ($student) {
            return $student['assignments']->whereIn('display_value', [
                    'Ontbreekt', 'Onvoldoende', 'Te laat', 'Hulp nodig'
                ])->count() > 0;
        });
    }

    public function getCompletionRate(): float
    {
        $totalAssignments = $this->getTotalAssignments();
        $totalStudents = $this->getTotalStudents();

        if ($totalAssignments === 0 || $totalStudents === 0) {
            return 0.0;
        }

        $completedCount = $this->studentsProgress->sum(function ($student) {
            return $student['assignments']->whereIn('status', ['graded', 'submitted'])->count();
        });

        return round(($completedCount / ($totalStudents * $totalAssignments)) * 100, 1);
    }

    public function getAveragePercentage(): float
    {
        $gradedAssignments = $this->studentsProgress->flatMap(function ($student) {
            return $student['assignments']->where('status', 'graded');
        });

        if ($gradedAssignments->isEmpty()) {
            return 0.0;
        }

        $totalPercentage = $gradedAssignments->sum(function ($assignment) {
            if ($assignment['points_possible'] > 0) {
                return ($assignment['score'] / $assignment['points_possible']) * 100;
            }
            return 0;
        });

        return round($totalPercentage / $gradedAssignments->count(), 1);
    }
}
