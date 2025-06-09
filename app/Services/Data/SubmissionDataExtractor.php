<?php

namespace App\Services\Data;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class SubmissionDataExtractor
{
    public function extract(array $students, array $courseModules): Collection
    {
        $submissions = collect();
        $studentIds = collect($students)->pluck('id');
        $transformedCourseModules = $this->transformCourseModules($courseModules);

        foreach ($transformedCourseModules as $courseId => $moduleIds) {
            $courseSubmissions = $this->getCourseSubmissions($courseId, $studentIds, $moduleIds);
            $submissions = $submissions->merge($courseSubmissions);
        }

        return $submissions;
    }

    protected function transformCourseModules(array $courseModules): array
    {
        return collect($courseModules)
            ->groupBy('course_id')
            ->map(fn($modules) => $modules->pluck('id')->toArray())
            ->toArray();
    }

    protected function getCourseSubmissions(int $courseId, Collection $studentIds, array $moduleIds): Collection
    {
        $submissions = collect();
        $assignmentIds = $this->getAssignmentIdsForModules($courseId, $moduleIds);

        foreach ($assignmentIds as $assignmentId) {
            foreach ($studentIds as $studentId) {
                $submission = $this->getStudentSubmission($courseId, $assignmentId, $studentId);
                if ($submission) {
                    $submissions->push($this->formatSubmission($submission, $courseId, $assignmentId, $studentId));
                }
            }
        }

        return $submissions;
    }

    protected function getAssignmentIdsForModules(int $courseId, array $moduleIds): array
    {
        $assignmentIds = [];

        foreach ($moduleIds as $moduleId) {
            try {
                $moduleItems = $this->getModuleItems($courseId, $moduleId);
                $moduleAssignments = collect($moduleItems)->where('type', 'Assignment');

                foreach ($moduleAssignments as $item) {
                    $assignmentIds[] = $item['content_id'];
                }
            } catch (\Exception $e) {
                Log::warning("Failed to get module items for course {$courseId}, module {$moduleId}");
            }
        }

        return array_unique($assignmentIds);
    }

    protected function getModuleItems(int $courseId, int $moduleId): array
    {
        $response = Http::withOptions(['verify' => false])
            ->withHeaders(['Authorization' => 'Bearer ' . env('CANVAS_API_TOKEN')])
            ->get(env('CANVAS_API_URL') . "/api/v1/courses/{$courseId}/modules/{$moduleId}/items", [
                'per_page' => 100,
            ]);

        return $response->successful() ? $response->json() : [];
    }

    protected function getStudentSubmission(int $courseId, int $assignmentId, int $studentId): ?array
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders(['Authorization' => 'Bearer ' . env('CANVAS_API_TOKEN')])
                ->get(env('CANVAS_API_URL') . "/api/v1/courses/{$courseId}/assignments/{$assignmentId}/submissions/{$studentId}");

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::warning("Failed to get submission for student {$studentId}, assignment {$assignmentId}");
            return null;
        }
    }

    protected function formatSubmission(?array $submission, int $courseId, int $assignmentId, int $studentId): array
    {
        return [
            'student_id' => $studentId,
            'course_id' => $courseId,
            'assignment_id' => $assignmentId,
            'workflow_state' => $submission['workflow_state'] ?? 'unsubmitted',
            'score' => $submission['score'] ?? null,
            'grade' => $submission['grade'] ?? null,
            'submitted_at' => $submission['submitted_at'] ?? null,
            'graded_at' => $submission['graded_at'] ?? null,
            'excused' => $submission['excused'] ?? false,
            'late' => $submission['late'] ?? false,
        ];
    }
}
