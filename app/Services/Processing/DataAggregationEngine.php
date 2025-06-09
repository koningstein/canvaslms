<?php

namespace App\Services\Processing;

use App\Configuration\ReportConfiguration;
use Illuminate\Support\Collection;

class DataAggregationEngine
{
    public function aggregate(Collection $studentsProgress, ReportConfiguration $config): Collection
    {
        // Apply filtering based on config
        if ($config->shouldShowOnlyProblematic()) {
            $studentsProgress = $this->filterProblematicStudents($studentsProgress);
        }

        // Calculate totals and statistics
        if ($config->aggregationRules['show_totals'] ?? false) {
            $studentsProgress = $this->addTotalStatistics($studentsProgress, $config);
        }

        // Calculate averages if needed
        if ($config->aggregationRules['calculate_averages'] ?? false) {
            $studentsProgress = $this->addAverageCalculations($studentsProgress, $config);
        }

        return $studentsProgress;
    }

    protected function filterProblematicStudents(Collection $studentsProgress): Collection
    {
        return $studentsProgress->filter(function ($student) {
            return $student['assignments']->whereIn('display_value', [
                    'Ontbreekt', 'Onvoldoende', 'Te laat', 'Hulp nodig'
                ])->count() > 0;
        });
    }

    protected function addTotalStatistics(Collection $studentsProgress, ReportConfiguration $config): Collection
    {
        $totalStudents = $studentsProgress->count();
        $totalAssignments = $studentsProgress->isNotEmpty() ?
            $studentsProgress->first()['assignments']->count() : 0;

        return $studentsProgress->map(function ($student) use ($totalStudents, $totalAssignments) {
            $student['total_students'] = $totalStudents;
            $student['total_assignments'] = $totalAssignments;
            return $student;
        });
    }

    protected function addAverageCalculations(Collection $studentsProgress, ReportConfiguration $config): Collection
    {
        return $studentsProgress->map(function ($student) use ($config) {
            $gradedAssignments = $student['assignments']->where('status', 'graded');
            $totalPercentage = 0;
            $count = 0;

            foreach ($gradedAssignments as $assignment) {
                if (isset($assignment['score'], $assignment['points_possible']) &&
                    $assignment['points_possible'] > 0) {
                    $percentage = ($assignment['score'] / $assignment['points_possible']) * 100;
                    $totalPercentage += $percentage;
                    $count++;
                }
            }

            $student['average_percentage'] = $count > 0 ? round($totalPercentage / $count, 1) : 0;
            $student['graded_count'] = $count;

            return $student;
        });
    }
}
