<?php

namespace App\Services\Data;

use App\Services\CanvasService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ModuleDataExtractor
{
    public function __construct(protected CanvasService $canvasService)
    {
    }

    public function extract(array $courseModules): Collection
    {
        return collect($courseModules)->map(function ($module) {
            return [
                'module_id' => $module['id'],
                'course_id' => $module['course_id'],
                'module_name' => $this->getCachedModuleName($module['course_id'], $module['id']),
                'course_name' => $module['course_name'] ?? 'Unknown Course',
            ];
        });
    }

    protected function getCachedModuleName(int $courseId, int $moduleId): string
    {
        $cacheKey = "module_name_{$courseId}_{$moduleId}";

        return Cache::remember($cacheKey, 600, function () use ($courseId, $moduleId) {
            $modules = $this->canvasService->getModules($courseId);
            $module = collect($modules)->firstWhere('id', $moduleId);

            return $module['name'] ?? "Module {$moduleId}";
        });
    }
}
