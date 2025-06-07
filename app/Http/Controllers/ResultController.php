<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResultController extends Controller
{
    public function getSelectedProgress()
    {
        $students = session('selected_users', []);
        $courseModules = session('selected_modules', []);

        // Transform the arrays into the required format
        $transformedStudents = collect($students)->pluck('id')->toArray();
        $transformedCourseModules = collect($courseModules)
            ->groupBy('course_id')
            ->map(function ($modules) {
                return $modules->pluck('id')->toArray();
            })
            ->toArray();

        try {
            $studentsProgress = collect();

            // Cache assignments and module names per course
            $allAssignments = [];
            $allModules = [];
            foreach ($transformedCourseModules as $courseId => $moduleIds) {
                // Fetch all assignments for the course
                $assignmentsResponse = Http::withOptions(['verify' => false])
                    ->withHeaders(['Authorization' => 'Bearer ' . env('CANVAS_API_TOKEN')])
                    ->get(env('CANVAS_API_URL') . "/api/v1/courses/{$courseId}/assignments", [
                        'per_page' => 100,
                    ]);

                $allAssignments[$courseId] = collect($assignmentsResponse->json())->keyBy('id');

                // Fetch module names
                foreach ($moduleIds as $moduleId) {
                    $moduleResponse = Http::withOptions(['verify' => false])
                        ->withHeaders(['Authorization' => 'Bearer ' . env('CANVAS_API_TOKEN')])
                        ->get(env('CANVAS_API_URL') . "/api/v1/courses/{$courseId}/modules/{$moduleId}");

                    if ($moduleResponse->successful()) {
                        $moduleData = $moduleResponse->json();
                        $allModules[$moduleId] = $moduleData['name'] ?? "Module {$moduleId}";
                    } else {
                        $allModules[$moduleId] = "Module {$moduleId}";
                    }
                }
            }

            foreach ($transformedStudents as $studentId) {
                $studentResponse = Http::withOptions(['verify' => false])
                    ->withHeaders(['Authorization' => 'Bearer ' . env('CANVAS_API_TOKEN')])
                    ->get(env('CANVAS_API_URL') . "/api/v1/users/{$studentId}/profile");

                $student = $studentResponse->json();
                $studentName = $student['name'] ?? "Unknown ({$studentId})";

                $assignmentsStatuses = collect();

                foreach ($transformedCourseModules as $courseId => $moduleIds) {
                    foreach ($moduleIds as $moduleId) {
                        // Fetch module items
                        $moduleItemsResponse = Http::withOptions(['verify' => false])
                            ->withHeaders(['Authorization' => 'Bearer ' . env('CANVAS_API_TOKEN')])
                            ->get(env('CANVAS_API_URL') . "/api/v1/courses/{$courseId}/modules/{$moduleId}/items", [
                                'per_page' => 100,
                            ]);

                        $moduleItems = collect($moduleItemsResponse->json());
                        $assignmentsInModule = $moduleItems->where('type', 'Assignment');

                        foreach ($assignmentsInModule as $assignment) {
                            $assignmentId = $assignment['content_id'];
                            $pointsPossible = $allAssignments[$courseId]->get($assignmentId)['points_possible'] ?? 1;

                            $submissionResponse = Http::withOptions(['verify' => false])
                                ->withHeaders(['Authorization' => 'Bearer ' . env('CANVAS_API_TOKEN')])
                                ->get(env('CANVAS_API_URL') . "/api/v1/courses/{$courseId}/assignments/{$assignmentId}/submissions/{$studentId}");

                            $submission = $submissionResponse->successful() ? $submissionResponse->json() : null;

                            $status = $submission['workflow_state'] ?? 'unsubmitted';
                            $score = $submission['score'] ?? 0;
                            $grade = strtolower($submission['grade'] ?? '');

                            $color = 'bg-red-300'; // Default red
                            if ($submission['excused'] ?? false) {
                                $color = 'bg-gray-100'; // Excused
                                $status = 'excused';
                            } elseif ($status === 'graded') {
                                if ($pointsPossible > 0) {
                                    if ($score >= 0.75 * $pointsPossible) {
                                        $color = 'bg-green-300';
                                    } elseif ($score >= 0.5 * $pointsPossible) {
                                        $color = 'bg-yellow-300';
                                    } else {
                                        $color = 'bg-orange-300';
                                    }
                                } else {
                                    $color = $grade === 'complete' ? 'bg-green-300' : 'bg-orange-300';
                                }
                            } elseif ($status === 'submitted') {
                                $color = 'bg-blue-300';
                            }

                            $assignmentsStatuses->push([
                                'assignment_name' => $assignment['title'] ?? "Assignment {$assignmentId}",
                                'module_id' => $moduleId,
                                'module_name' => $allModules[$moduleId] ?? "Module {$moduleId}",
                                'status' => $status,
                                'color' => $color,
                            ]);
                        }
                    }
                }

                $studentsProgress->push([
                    'student_id' => $studentId,
                    'student_name' => $studentName,
                    'assignments' => $assignmentsStatuses,
                ]);
            }

            return view('results.progress-overview', [
                'studentsProgress' => $studentsProgress,
            ]);
        } catch (\Exception $e) {
            Log::error('Canvas API Error', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
