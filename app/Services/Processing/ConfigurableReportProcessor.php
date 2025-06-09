<?php

namespace App\Services\Processing;

use App\Configuration\ReportConfiguration;
use App\Services\Data\StudentDataExtractor;
use App\Services\Data\AssignmentDataExtractor;
use App\Services\Data\SubmissionDataExtractor;
use Illuminate\Support\Collection;

class ConfigurableReportProcessor
{
    public function __construct(
        protected StudentDataExtractor $studentExtractor,
        protected AssignmentDataExtractor $assignmentExtractor,
        protected SubmissionDataExtractor $submissionExtractor,
        protected DataAggregationEngine $aggregationEngine,
        protected ColorStatusEngine $colorEngine,
        protected ResultFormatterEngine $formatterEngine
    ) {}

    public function processReport(
        array $students,
        array $courseModules,
        ReportConfiguration $config
    ): Collection {
        // Extract raw data
        $studentsData = $this->studentExtractor->extract($students);
        $assignmentsData = $this->assignmentExtractor->extract($courseModules);
        $submissionsData = $this->submissionExtractor->extract($students, $courseModules);

        // Process each student
        $processedStudents = collect();

        foreach ($studentsData as $student) {
            $studentAssignments = $this->processStudentAssignments(
                $student,
                $assignmentsData,
                $submissionsData,
                $config
            );

            $processedStudents->push([
                'student_id' => $student['student_id'],
                'canvas_id' => $student['canvas_id'],
                'student_name' => $student['student_name'],
                'sis_user_id' => $student['sis_user_id'],
                'assignments' => $studentAssignments,
            ]);
        }

        // Apply aggregation and formatting
        return $this->aggregationEngine->aggregate($processedStudents, $config);
    }

    protected function processStudentAssignments(
        array $student,
        Collection $assignmentsData,
        Collection $submissionsData,
        ReportConfiguration $config
    ): Collection {
        $studentAssignments = collect();

        foreach ($assignmentsData as $assignment) {
            $submission = $submissionsData
                ->where('student_id', $student['canvas_id'])
                ->where('assignment_id', $assignment['assignment_id'])
                ->first();

            $colorStatus = $this->colorEngine->calculate($assignment, $submission, $config);
            $formattedResult = $this->formatterEngine->format($assignment, $submission, $colorStatus, $config);

            $studentAssignments->push($formattedResult);
        }

        return $studentAssignments;
    }
}
