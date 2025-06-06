<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
}
