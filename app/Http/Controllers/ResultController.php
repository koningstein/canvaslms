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
        $reportType = session('report_type', 'basic');

        // Transform the arrays into the required format
        $transformedStudents = collect($students)->pluck('id')->toArray();
        // Keep original student data for names and IDs
        $studentData = collect($students)->keyBy('id');

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
                // Get student info from session data instead of API call
                $studentInfo = $studentData->get($studentId);
                $studentName = $studentInfo['name'] ?? "Unknown ({$studentId})";

                // Try to get student number - prioritize integration_id
                $studentNumber = $studentInfo['integration_id'] ??
                    $studentInfo['sis_user_id'] ??
                    $studentInfo['login_id'] ??
                    $studentId;

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
                            $assignmentDetails = $allAssignments[$courseId]->get($assignmentId);
                            $pointsPossible = $assignmentDetails['points_possible'] ?? 1;
                            $submissionTypes = $assignmentDetails['submission_types'] ?? [];

                            $submissionResponse = Http::withOptions(['verify' => false])
                                ->withHeaders(['Authorization' => 'Bearer ' . env('CANVAS_API_TOKEN')])
                                ->get(env('CANVAS_API_URL') . "/api/v1/courses/{$courseId}/assignments/{$assignmentId}/submissions/{$studentId}");

                            $submission = $submissionResponse->successful() ? $submissionResponse->json() : null;

                            $status = $submission['workflow_state'] ?? 'unsubmitted';
                            $score = $submission['score'] ?? 0;
                            $grade = strtolower($submission['grade'] ?? '');

                            // Color and status logic based on report type
                            $colorAndStatus = $this->getColorAndStatus($submission, $status, $score, $grade, $pointsPossible, $reportType, $assignmentDetails, $submissionTypes);

                            $assignmentsStatuses->push([
                                'assignment_name' => $assignment['title'] ?? "Assignment {$assignmentId}",
                                'assignment_id' => $assignmentId,
                                'module_id' => $moduleId,
                                'module_name' => $allModules[$moduleId] ?? "Module {$moduleId}",
                                'status' => $colorAndStatus['status'],
                                'color' => $colorAndStatus['color'],
                                'display_value' => $colorAndStatus['display_value'],
                                'score' => $score,
                                'points_possible' => $pointsPossible,
                                'submitted_at' => $submission['submitted_at'] ?? null,
                                'graded_at' => $submission['graded_at'] ?? null,
                                'due_at' => $assignmentDetails['due_at'] ?? null,
                            ]);
                        }
                    }
                }

                $studentsProgress->push([
                    'student_id' => $studentNumber,
                    'canvas_id' => $studentId,
                    'student_name' => $studentName,
                    'assignments' => $assignmentsStatuses,
                ]);
            }

            // Choose the appropriate view based on report type
            return $this->renderReportView($reportType, $studentsProgress);

        } catch (\Exception $e) {
            Log::error('Canvas API Error', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getColorAndStatus($submission, $status, $score, $grade, $pointsPossible, $reportType, $assignmentDetails, $submissionTypes = [])
    {
        switch ($reportType) {
            case 'basic':
                return $this->getBasicColorStatus($submission, $status, $score, $grade, $pointsPossible, $submissionTypes);
            case 'grades':
                return $this->getGradeColorStatus($submission, $status, $score, $grade, $pointsPossible, $submissionTypes);
            case 'percentages':
                return $this->getPercentageColorStatus($submission, $status, $score, $grade, $pointsPossible, $submissionTypes);
            case 'missing':
                return $this->getMissingColorStatus($submission, $status, $score, $grade, $pointsPossible, $submissionTypes);
            case 'attention':
                return $this->getAttentionColorStatus($submission, $status, $score, $grade, $pointsPossible, $submissionTypes);
            default:
                return $this->getBasicColorStatus($submission, $status, $score, $grade, $pointsPossible, $submissionTypes);
        }
    }

    private function getBasicColorStatus($submission, $status, $score, $grade, $pointsPossible, $submissionTypes = [])
    {
        $color = 'bg-red-300'; // Default red for unsubmitted
        $displayValue = '';

        // Check if this is a non-submittable assignment (no submission types or contains 'none')
        $isNonSubmittable = empty($submissionTypes) || in_array('none', $submissionTypes);

        // Check if there's actually a grade/score assigned
        $hasGrade = ($status === 'graded') ||
            ($score !== null && $score > 0) ||
            (!empty($grade) && $grade !== 'null');

        if ($submission['excused'] ?? false) {
            $color = 'bg-gray-300';
            $status = 'excused';
            $displayValue = 'Vrijgesteld';
        } elseif ($hasGrade) {
            // There's an actual grade/score assigned
            if ($pointsPossible > 0) {
                $percentage = ($score / $pointsPossible) * 100;
                if ($percentage >= 75) {
                    $color = 'bg-green-400';
                    $displayValue = 'Goed';
                } elseif ($percentage >= 55) {
                    $color = 'bg-yellow-400';
                    $displayValue = 'Voldoende';
                } else {
                    $color = 'bg-orange-400';
                    $displayValue = 'Onvoldoende';
                }
            } else {
                // Pass/Fail assignment
                $color = $grade === 'complete' ? 'bg-green-400' : 'bg-orange-400';
                $displayValue = $grade === 'complete' ? 'Voltooid' : 'Niet voltooid';
            }
        } elseif ($status === 'submitted') {
            $color = 'bg-blue-400';
            $displayValue = 'Ingeleverd';
        } elseif ($isNonSubmittable) {
            // Only show "no submission" if there's no grade yet and it's not submittable
            $color = 'bg-gray-200';
            $displayValue = 'Geen inlevering';
        } else {
            $color = 'bg-red-300';
            $displayValue = 'Niet ingeleverd';
        }

        return [
            'color' => $color,
            'status' => $status,
            'display_value' => $displayValue
        ];
    }

    private function getGradeColorStatus($submission, $status, $score, $grade, $pointsPossible)
    {
        // Implementation for grade report
        $color = 'bg-gray-200';
        $displayValue = '-';

        if ($submission['excused'] ?? false) {
            $color = 'bg-gray-300';
            $displayValue = 'Vrijgesteld';
        } elseif ($status === 'graded' && $score !== null) {
            $displayValue = number_format($score, 1);
            if ($pointsPossible > 0) {
                $percentage = ($score / $pointsPossible) * 100;
                if ($percentage >= 75) {
                    $color = 'bg-green-200';
                } elseif ($percentage >= 55) {
                    $color = 'bg-yellow-200';
                } else {
                    $color = 'bg-red-200';
                }
            }
        } elseif ($status === 'submitted') {
            $color = 'bg-blue-200';
            $displayValue = 'Ingeleverd';
        }

        return [
            'color' => $color,
            'status' => $status,
            'display_value' => $displayValue
        ];
    }

    private function getPercentageColorStatus($submission, $status, $score, $grade, $pointsPossible)
    {
        // Implementation for percentage report
        $color = 'bg-gray-200';
        $displayValue = '-';

        if ($submission['excused'] ?? false) {
            $color = 'bg-gray-300';
            $displayValue = 'Vrijgesteld';
        } elseif ($status === 'graded' && $score !== null && $pointsPossible > 0) {
            $percentage = ($score / $pointsPossible) * 100;
            $displayValue = number_format($percentage, 0) . '%';

            if ($percentage >= 75) {
                $color = 'bg-green-200';
            } elseif ($percentage >= 55) {
                $color = 'bg-yellow-200';
            } else {
                $color = 'bg-red-200';
            }
        } elseif ($status === 'submitted') {
            $color = 'bg-blue-200';
            $displayValue = 'Ingeleverd';
        }

        return [
            'color' => $color,
            'status' => $status,
            'display_value' => $displayValue
        ];
    }

    // Vervang de getMissingColorStatus methode in ResultController.php

    private function getMissingColorStatus($submission, $status, $score, $grade, $pointsPossible, $submissionTypes = [])
    {
        // Only show missing/incomplete assignments and late submissions
        $color = 'bg-white';
        $displayValue = '';

        // Check if assignment was submitted late
        $isLate = false;
        if (isset($submission['submitted_at'], $submission['due_at'])) {
            $submittedAt = strtotime($submission['submitted_at']);
            $dueAt = strtotime($submission['due_at']);
            $isLate = $submittedAt > $dueAt;
        }

        // Check if this is a non-submittable assignment
        $isNonSubmittable = empty($submissionTypes) || in_array('none', $submissionTypes);

        // Excused assignments don't show as missing
        if ($submission['excused'] ?? false) {
            return [
                'color' => 'bg-white',
                'status' => 'excused',
                'display_value' => ''
            ];
        }

        // Non-submittable assignments without grades don't count as missing
        if ($isNonSubmittable && !($status === 'graded' && $score !== null)) {
            return [
                'color' => 'bg-white',
                'status' => $status,
                'display_value' => ''
            ];
        }

        // Check for unsubmitted assignments
        if ($status === 'unsubmitted' || $status === 'pending_review') {
            $color = 'bg-red-400';
            $displayValue = 'Ontbreekt';
        }
        // Check for insufficient grades
        elseif ($status === 'graded' && $score !== null && $pointsPossible > 0) {
            $percentage = ($score / $pointsPossible) * 100;
            if ($percentage < 55) {
                $color = 'bg-orange-400';
                $displayValue = 'Onvoldoende';
            }
            // Even sufficient grades show as late if submitted late
            elseif ($isLate) {
                $color = 'bg-yellow-300';
                $displayValue = 'Te laat';
            }
        }
        // Check for late submissions (even if graded sufficiently)
        elseif ($isLate && in_array($status, ['submitted', 'graded'])) {
            $color = 'bg-yellow-300';
            $displayValue = 'Te laat';
        }

        return [
            'color' => $color,
            'status' => $status,
            'display_value' => $displayValue
        ];
    }

    private function getAttentionColorStatus($submission, $status, $score, $grade, $pointsPossible)
    {
        // Highlight students who need attention
        $color = 'bg-white';
        $displayValue = '';

        if ($status === 'unsubmitted') {
            $color = 'bg-red-400';
            $displayValue = 'Hulp nodig';
        } elseif ($status === 'graded' && $pointsPossible > 0) {
            $percentage = ($score / $pointsPossible) * 100;
            if ($percentage < 55) {
                $color = 'bg-orange-400';
                $displayValue = 'Extra begeleiding';
            } elseif ($percentage < 75) {
                $color = 'bg-yellow-400';
                $displayValue = 'Aandacht';
            }
        } elseif ($status === 'submitted') {
            $color = 'bg-blue-300';
            $displayValue = 'Nakijken';
        }

        return [
            'color' => $color,
            'status' => $status,
            'display_value' => $displayValue
        ];
    }

    private function renderReportView($reportType, $studentsProgress)
    {
        $viewData = [
            'studentsProgress' => $studentsProgress,
            'reportType' => $reportType
        ];

        switch ($reportType) {
            case 'basic':
                return view('results.basic-color-report', $viewData);
            case 'grades':
                return view('results.grades-report', $viewData);
            case 'percentages':
                return view('results.percentages-report', $viewData);
            case 'missing':
                return view('results.missing-report', $viewData);
            case 'attention':
                return view('results.attention-report', $viewData);
            default:
                return view('results.basic-color-report', $viewData);
        }
    }
}
