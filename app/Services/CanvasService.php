<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CanvasService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('canvas.api_url');
        $this->token = config('canvas.api_token');

        if (!$this->baseUrl || !$this->token) {
            throw new \Exception('Canvas API configuratie ontbreekt. Controleer je .env bestand.');
        }
    }

    /**
     * Zoek cursussen op basis van een zoekterm
     */
    public function searchCourses(string $searchTerm = '')
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                ->get("{$this->baseUrl}/api/v1/courses", [
                    'search_term' => $searchTerm,
                    'per_page' => 200,
                    'enrollment_type' => 'teacher',
                    'state' => ['available', 'completed'],
                ]);

            if ($response->failed()) {
                Log::error('Canvas API fout', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new \Exception('Fout bij het ophalen van cursussen');
            }

            return collect($response->json());

        } catch (\Exception $e) {
            Log::error('Canvas Service fout', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getModules($courseId)
    {
        $cacheKey = "canvas_modules_{$courseId}";
        return Cache::remember($cacheKey, 600, function () use ($courseId) {
            try {
                $response = Http::withOptions(['verify' => false])
                    ->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                    ->get("{$this->baseUrl}/api/v1/courses/{$courseId}/modules", [
                        'per_page' => 100,
                    ]);
                if ($response->failed()) {
                    throw new \Exception('Fout bij het ophalen van modules');
                }
                return $response->json();
            } catch (\Exception $e) {
                \Log::error('Canvas Service fout (modules)', ['message' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Haal assignment groups op voor een cursus
     */
    public function getAssignmentGroups($courseId)
    {
        $cacheKey = "canvas_assignment_groups_{$courseId}";
        return Cache::remember($cacheKey, 600, function () use ($courseId) {
            try {
                $response = Http::withOptions(['verify' => false])
                    ->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                    ->get("{$this->baseUrl}/api/v1/courses/{$courseId}/assignment_groups", [
                        'per_page' => 100,
                        'include[]' => 'assignments', // Dit zorgt ervoor dat assignments worden meegeleverd
                    ]);

                if ($response->failed()) {
                    throw new \Exception('Fout bij het ophalen van assignment groups');
                }

                return $response->json();
            } catch (\Exception $e) {
                \Log::error('Canvas Service fout (assignment groups)', ['message' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Haal module items op voor een specifieke module
     */
    public function getModuleItems($courseId, $moduleId)
    {
        $cacheKey = "canvas_module_items_{$courseId}_{$moduleId}";
        return Cache::remember($cacheKey, 600, function () use ($courseId, $moduleId) {
            try {
                $response = Http::withOptions(['verify' => false])
                    ->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                    ->get("{$this->baseUrl}/api/v1/courses/{$courseId}/modules/{$moduleId}/items", [
                        'per_page' => 100,
                    ]);

                if ($response->failed()) {
                    throw new \Exception('Fout bij het ophalen van module items');
                }

                return $response->json();
            } catch (\Exception $e) {
                \Log::error('Canvas Service fout (module items)', ['message' => $e->getMessage()]);
                return [];
            }
        });
    }

    public function getUsers($courseId)
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                ->get("{$this->baseUrl}/api/v1/courses/{$courseId}/users", [
                    'per_page' => 100,
                    'enrollment_type' => 'student',
                ]);

            if ($response->failed()) {
                throw new \Exception('Fout bij het ophalen van gebruikers');
            }

            return $response->json();
        } catch (\Exception $e) {
            \Log::error('Canvas Service fout (users)', ['message' => $e->getMessage()]);
            return [];
        }
    }
}
