<?php

namespace App\Services\Data;

use App\Services\CanvasService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AssignmentDataExtractor
{
    public function __construct(protected CanvasService $canvasService)
    {
    }

    public function extract(array $courseModules): Collection
    {
        $assignments = collect();
        $transformedCourseModules = $this->transformCourseModules($courseModules);

        foreach ($transformedCourseModules as $courseId => $moduleIds) {
            $courseAssignments = $this->getCachedAssignments($courseId);
            $moduleNames = $this->getCachedModuleNames($courseId, $moduleIds);

            foreach ($moduleIds as $moduleId) {
                $moduleAssignments = $this->getModuleAssignments($courseId, $moduleId, $courseAssignments, $moduleNames);
                $assignments = $assignments->merge($moduleAssignments);
            }
        }

        return $assignments;
    }

    protected function transformCourseModules(array $courseModules): array
    {
        return collect($courseModules)
            ->groupBy('course_id')
            ->map(fn($modules) => $modules->pluck('id')->toArray())
            ->toArray();
    }

    protected function getCachedAssignments(int $courseId): Collection
    {
        $cacheKey = "assignments_{$courseId}";
        return Cache::remember($cacheKey, 600, function () use ($courseId) {
            $assignments = $this->canvasService->getAssignmentGroups($courseId);
            return collect($assignments)->flatMap(fn($group) => $group['assignments'] ?? [])->keyBy('id');
        });
    }

    protected function getCachedModuleNames(int $courseId, array $moduleIds): array
    {
        $moduleNames = [];
        foreach ($moduleIds as $moduleId) {
            $cacheKey = "module_name_{$courseId}_{$moduleId}";
            $moduleNames[$moduleId] = Cache::remember($cacheKey, 600, function () use ($courseId, $moduleId) {
                $modules = $this->canvasService->getModules($courseId);
                $module = collect($modules)->firstWhere('id', $moduleId);
                return $module['name'] ?? "Module {$moduleId}";
            });
        }
        return $moduleNames;
    }

    protected function getModuleAssignments(int $courseId, int $moduleId, Collection $courseAssignments, array $moduleNames): Collection
    {
        $moduleItems = $this->canvasService->getModuleItems($courseId, $moduleId);
        $assignmentItems = collect($moduleItems)->where('type', 'Assignment');

        return $assignmentItems->map(function ($item) use ($courseId, $moduleId, $courseAssignments, $moduleNames) {
            $assignmentId = $item['content_id'];
            $assignmentDetails = $courseAssignments->get($assignmentId);

            if (!$assignmentDetails) return null;

            return [
                'assignment_id' => $assignmentId,
                'assignment_name' => $item['title'] ?? "Assignment {$assignmentId}",
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'module_name' => $moduleNames[$moduleId],
                'points_possible' => $assignmentDetails['points_possible'] ?? 0,
                'due_at' => $assignmentDetails['due_at'] ?? null,
                'submission_types' => $assignmentDetails['submission_types'] ?? [],
                'assignment_group_name' => $assignmentDetails['assignment_group']['name'] ?? 'Unknown',
            ];
        })->filter();
    }
}
