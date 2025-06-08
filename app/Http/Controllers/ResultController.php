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

    private function getGradeColorStatus($submission, $status, $score, $grade, $pointsPossible, $submissionTypes = [])
    {
        $color = 'bg-gray-200';
        $displayValue = '-';

        // Check if this is a non-submittable assignment
        $isNonSubmittable = empty($submissionTypes) || in_array('none', $submissionTypes);

        if ($submission['excused'] ?? false) {
            $color = 'bg-gray-300';
            $displayValue = 'Vrijgesteld';
        } elseif ($status === 'graded' && $score !== null) {
            // Show actual score for graded assignments
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
            } else {
                // For pass/fail assignments
                $color = $grade === 'complete' ? 'bg-green-200' : 'bg-red-200';
                $displayValue = $grade === 'complete' ? 'Voltooid' : 'Niet voltooid';
            }
        } elseif ($status === 'submitted') {
            $color = 'bg-blue-200';
            $displayValue = 'Ingeleverd';
        } elseif ($isNonSubmittable && ($pointsPossible ?? 0) > 0) {
            // Non-submittable assignment without grade
            $color = 'bg-gray-200';
            $displayValue = 'Nog geen cijfer';
        } else {
            // Regular assignment not submitted
            $color = 'bg-gray-200';
            $displayValue = 'Niet ingeleverd';
        }

        return [
            'color' => $color,
            'status' => $status,
            'display_value' => $displayValue
        ];
    }

    private function getPercentageColorStatus($submission, $status, $score, $grade, $pointsPossible, $submissionTypes = [])
    {
        $color = 'bg-orange-200';
        $displayValue = '0%';

        // Check if this is a non-submittable assignment
        $isNonSubmittable = empty($submissionTypes) || in_array('none', $submissionTypes);

        if ($submission['excused'] ?? false) {
            $color = 'bg-purple-200';
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
        } elseif ($status === 'graded' && $pointsPossible == 0) {
            // Pass/fail assignment
            $color = $grade === 'complete' ? 'bg-green-200' : 'bg-red-200';
            $displayValue = $grade === 'complete' ? '100%' : '0%';
        } elseif ($status === 'submitted') {
            $color = 'bg-blue-200';
            $displayValue = '0%';
        } elseif ($isNonSubmittable && ($pointsPossible ?? 0) > 0) {
            // Non-submittable assignment without grade
            $color = 'bg-orange-200';
            $displayValue = '0%';
        } else {
            // Regular assignment not submitted
            $color = 'bg-orange-200';
            $displayValue = '0%';
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

    private function calculateMissingReportData($studentsProgress)
    {
        $totalStudents = $studentsProgress->count();
        $totalAssignments = $studentsProgress->isNotEmpty() ? $studentsProgress->first()['assignments']->count() : 0;

        // Count missing assignments (unsubmitted)
        $totalMissing = $studentsProgress->sum(function($student) {
            return $student['assignments']->where('status', 'unsubmitted')->count();
        });

        // Count insufficient assignments (graded but < 55%)
        $totalInsufficient = $studentsProgress->sum(function($student) {
            return $student['assignments']->filter(function($assignment) {
                return $assignment['status'] === 'graded' &&
                    isset($assignment['score']) &&
                    isset($assignment['points_possible']) &&
                    $assignment['points_possible'] > 0 &&
                    ($assignment['score'] / $assignment['points_possible'] * 100) < 55;
            })->count();
        });

        $totalProblematic = $totalMissing + $totalInsufficient;
        $totalPossible = $totalStudents * $totalAssignments;
        $problemRate = $totalPossible > 0 ? round(($totalProblematic / $totalPossible) * 100, 1) : 0;

        // Students with any missing assignments
        $studentsWithMissing = $studentsProgress->filter(function($student) {
            return $student['assignments']->whereIn('display_value', ['Ontbreekt', 'Onvoldoende', 'Te laat'])->count() > 0;
        });

        // Count students with problems
        $studentsWithProblemsCount = $studentsWithMissing->count();

        // Get assignment groups for header
        $assignmentGroups = [];
        if ($studentsProgress->isNotEmpty()) {
            $assignmentGroups = $studentsProgress->first()['assignments']->groupBy('module_name');
        }

        // Process students with problems - add all display logic here
        $studentsWithProblems = $studentsWithMissing->map(function($student) use ($assignmentGroups) {
            $problemCount = $student['assignments']->whereIn('display_value', ['Ontbreekt', 'Onvoldoende', 'Te laat'])->count();

            // Process each assignment for this student
            $processedAssignments = [];
            foreach ($assignmentGroups as $moduleName => $assignments) {
                foreach ($assignments as $assignment) {
                    $studentAssignment = $student['assignments']->where('assignment_name', $assignment['assignment_name'])->first();

                    $isProblematic = in_array($studentAssignment['display_value'] ?? '', ['Ontbreekt', 'Onvoldoende', 'Te laat']);

                    // Check if assignment is late
                    $isLate = false;
                    if (isset($studentAssignment['submitted_at'], $studentAssignment['due_at'])) {
                        $submittedAt = strtotime($studentAssignment['submitted_at']);
                        $dueAt = strtotime($studentAssignment['due_at']);
                        $isLate = $submittedAt > $dueAt;
                    }

                    // Determine cell color and display value
                    $cellColor = $studentAssignment['color'] ?? 'bg-white';
                    $displayValue = $studentAssignment['display_value'] ?? '';
                    $showLateIcon = false;

                    if ($isLate && in_array($studentAssignment['status'] ?? '', ['graded', 'submitted'])) {
                        $cellColor = 'bg-yellow-300';
                        $displayValue = 'Te laat';
                        $showLateIcon = true;
                    }

                    // Only show problematic assignments
                    if (!$isProblematic && !$isLate) {
                        $cellColor = 'bg-white';
                        $displayValue = '';
                        $showLateIcon = false;
                    }

                    $processedAssignments[] = [
                        'assignment_name' => $assignment['assignment_name'],
                        'module_name' => $moduleName,
                        'cell_color' => $cellColor,
                        'display_value' => $displayValue,
                        'show_late_icon' => $showLateIcon,
                        'tooltip' => $assignment['assignment_name'] . ' - ' . $displayValue . ($isLate ? ' (Te laat ingeleverd)' : '')
                    ];
                }
            }

            $student['problem_count'] = $problemCount;
            $student['processed_assignments'] = $processedAssignments;
            return $student;
        });

        return [
            'totalStudents' => $totalStudents,
            'totalMissing' => $totalMissing,
            'totalInsufficient' => $totalInsufficient,
            'totalProblematic' => $totalProblematic,
            'studentsWithProblemsCount' => $studentsWithProblemsCount,
            'problemRate' => $problemRate,
            'studentsWithProblems' => $studentsWithProblems,
            'assignmentGroups' => $assignmentGroups
        ];
    }

    private function getAttentionColorStatus($submission, $status, $score, $grade, $pointsPossible)
    {
        // Voor attention rapport gebruiken we een eenvoudigere kleurlogica
        $color = 'bg-white';
        $displayValue = '';

        // Excused assignments don't show as risky
        if ($submission['excused'] ?? false) {
            return [
                'color' => 'bg-white',
                'status' => 'excused',
                'display_value' => ''
            ];
        }

        // Check submission types
        $isNonSubmittable = empty($submissionTypes) || in_array('none', $submissionTypes);

        if ($status === 'unsubmitted' && !$isNonSubmittable) {
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


    private function calculateAttentionRisks($studentsProgress)
    {
        $studentsWithRisk = collect();

        foreach($studentsProgress as $student) {
            $missing = 0;
            $insufficient = 0;
            $needsGrading = 0;
            $needsReview = 0;
            $late = 0;

            // Lists for detailed view
            $missingList = [];
            $insufficientList = [];
            $needsGradingList = [];
            $lateList = [];

            // Group assignments by assignment group to detect linked assignments
            $assignmentsByGroup = [];
            foreach($student['assignments'] as $assignment) {
                $groupName = $assignment['assignment_group_name'] ?? 'Unknown';
                if (!isset($assignmentsByGroup[$groupName])) {
                    $assignmentsByGroup[$groupName] = [];
                }
                $assignmentsByGroup[$groupName][] = $assignment;
            }

            foreach($assignmentsByGroup as $groupName => $assignments) {
                // Check if this group has any submitted/graded assignments
                $hasSubmissions = false;
                foreach($assignments as $assignment) {
                    if (in_array($assignment['status'], ['submitted', 'graded'])) {
                        $hasSubmissions = true;
                        break;
                    }
                }

                foreach($assignments as $assignment) {
                    if (isset($assignment['excused']) && $assignment['excused']) continue;

                    $submissionTypes = $assignment['submission_types'] ?? [];
                    $isNonSubmittable = empty($submissionTypes) || in_array('none', $submissionTypes);
                    $hasGrade = ($assignment['status'] === 'graded') ||
                        ($assignment['score'] !== null && $assignment['score'] > 0) ||
                        (!empty($assignment['grade']) && $assignment['grade'] !== 'null');

                    // Count missing (submittable but not submitted)
                    if (!$isNonSubmittable && $assignment['status'] === 'unsubmitted') {
                        $missing++;
                        $missingList[] = $assignment['assignment_name'];
                    }

                    // Count insufficient grades - all show as insufficient, but scoring differs
                    if ($assignment['status'] === 'graded' &&
                        isset($assignment['score']) &&
                        isset($assignment['points_possible']) &&
                        $assignment['points_possible'] > 0 &&
                        ($assignment['score'] / $assignment['points_possible'] * 100) < 55) {

                        // Always count as insufficient for display
                        $insufficient++;
                        $percentage = round(($assignment['score'] / $assignment['points_possible']) * 100);
                        $insufficientList[] = $assignment['assignment_name'] . ' (' . $percentage . '%)';
                    }

                    // Count non-submittable assignments needing grading
                    if ($isNonSubmittable && !$hasGrade && ($assignment['points_possible'] ?? 0) > 0) {
                        $needsGrading++;
                        $needsGradingList[] = $assignment['assignment_name'];
                    }

                    // Count submitted assignments needing review
                    if ($assignment['status'] === 'submitted') {
                        $needsReview++;
                    }

                    // Count late submissions (only if result is still problematic)
                    if (isset($assignment['submitted_at']) && isset($assignment['due_at'])) {
                        $submittedAt = strtotime($assignment['submitted_at']);
                        $dueAt = strtotime($assignment['due_at']);
                        $isLate = $submittedAt > $dueAt;

                        if ($isLate) {
                            $isStillProblematic = false;

                            if ($assignment['status'] === 'submitted') {
                                $isStillProblematic = true;
                            } elseif ($assignment['status'] === 'graded' &&
                                isset($assignment['score']) &&
                                isset($assignment['points_possible']) &&
                                $assignment['points_possible'] > 0) {
                                $percentage = ($assignment['score'] / $assignment['points_possible']) * 100;
                                if ($percentage < 55) {
                                    $isStillProblematic = true;
                                }
                            }

                            if ($isStillProblematic) {
                                $late++;
                                $lateList[] = $assignment['assignment_name'];
                            }
                        }
                    }
                }
            }

            // Smart risk calculation: lower score for insufficient in groups with submissions
            $insufficientRiskScore = 0;
            foreach($assignmentsByGroup as $groupName => $assignments) {
                $hasSubmissions = false;
                foreach($assignments as $assignment) {
                    if (in_array($assignment['status'], ['submitted', 'graded'])) {
                        $hasSubmissions = true;
                        break;
                    }
                }

                foreach($assignments as $assignment) {
                    if (isset($assignment['excused']) && $assignment['excused']) continue;

                    $submissionTypes = $assignment['submission_types'] ?? [];
                    $isNonSubmittable = empty($submissionTypes) || in_array('none', $submissionTypes);

                    if ($assignment['status'] === 'graded' &&
                        isset($assignment['score']) &&
                        isset($assignment['points_possible']) &&
                        $assignment['points_possible'] > 0 &&
                        ($assignment['score'] / $assignment['points_possible'] * 100) < 55) {

                        // Lower risk score if this is a non-submittable in group with submissions
                        if ($isNonSubmittable && $hasSubmissions) {
                            $insufficientRiskScore += 1; // Lower weight
                        } else {
                            $insufficientRiskScore += 3; // Normal weight
                        }
                    }
                }
            }

            // Calculate total risk score
            $riskScore = ($missing * 3) + $insufficientRiskScore + ($late * 1) + ($needsGrading * 1) + ($needsReview * 1);

            if ($riskScore > 0) {
                // Adjusted thresholds for realistic urgency
                $riskLevel = $riskScore >= 5 ? 'urgent' : ($riskScore >= 3 ? 'high' : 'medium');

                $studentsWithRisk->push([
                    'student' => $student,
                    'risk_score' => $riskScore,
                    'risk_level' => $riskLevel,
                    'missing' => $missing,
                    'insufficient' => $insufficient,
                    'needs_grading' => $needsGrading,
                    'needs_review' => $needsReview,
                    'late' => $late,
                    'missing_list' => $missingList,
                    'insufficient_list' => $insufficientList,
                    'needs_grading_list' => $needsGradingList,
                    'late_list' => $lateList
                ]);
            }
        }

        return $studentsWithRisk->sortByDesc('risk_score');
    }

    private function calculateGradesReportData($studentsProgress)
    {
        $totalStudents = $studentsProgress->count();
        $totalAssignments = $studentsProgress->isNotEmpty() ? $studentsProgress->first()['assignments']->count() : 0;

        // Calculate total points awarded and possible
        $totalPointsAwarded = 0;
        $totalPointsPossible = 0;

        foreach ($studentsProgress as $student) {
            foreach ($student['assignments'] as $assignment) {
                if (isset($assignment['score']) && is_numeric($assignment['score'])) {
                    $totalPointsAwarded += $assignment['score'];
                }
                if (isset($assignment['points_possible']) && is_numeric($assignment['points_possible'])) {
                    $totalPointsPossible += $assignment['points_possible'];
                }
            }
        }

        $averagePercentage = $totalPointsPossible > 0 ? round(($totalPointsAwarded / $totalPointsPossible) * 100, 1) : 0;

        // Get assignment groups for header
        $assignmentGroups = [];
        if ($studentsProgress->isNotEmpty()) {
            $assignmentGroups = $studentsProgress->first()['assignments']->groupBy('module_name')->map(function ($assignments) {
                return $assignments->map(function ($assignment) {
                    return [
                        'assignment_name' => $assignment['assignment_name'],
                        'assignment_id' => $assignment['assignment_id'],
                        'points_possible' => $assignment['points_possible'] ?? 0,
                    ];
                });
            });
        }

        // Process students with detailed score information
        $studentsWithScores = $studentsProgress->map(function ($student) use ($assignmentGroups) {
            $processedAssignments = [];
            $studentTotalScore = 0;
            $studentTotalPossible = 0;

            foreach ($assignmentGroups as $moduleName => $assignments) {
                foreach ($assignments as $assignment) {
                    $studentAssignment = $student['assignments']->where('assignment_name', $assignment['assignment_name'])->first();

                    $score = $studentAssignment['score'] ?? 0;
                    $pointsPossible = $assignment['points_possible'];
                    $status = $studentAssignment['status'] ?? 'unsubmitted';
                    $color = $studentAssignment['color'] ?? 'bg-gray-200';
                    $displayValue = $studentAssignment['display_value'] ?? '-';

                    // Calculate tooltip information
                    $tooltip = $assignment['assignment_name'];
                    if ($status === 'graded' && is_numeric($score) && $pointsPossible > 0) {
                        $percentage = round(($score / $pointsPossible) * 100, 1);
                        $tooltip .= " - {$score}/{$pointsPossible} punten ({$percentage}%)";
                    } else {
                        $tooltip .= " - {$displayValue}";
                    }

                    // Add to student totals (only for graded assignments)
                    if ($status === 'graded' && is_numeric($score)) {
                        $studentTotalScore += $score;
                    }
                    if ($pointsPossible > 0) {
                        $studentTotalPossible += $pointsPossible;
                    }

                    $processedAssignments[] = [
                        'assignment_name' => $assignment['assignment_name'],
                        'module_name' => $moduleName,
                        'color' => $color,
                        'display_value' => $displayValue,
                        'points_possible' => $pointsPossible,
                        'show_points_possible' => ($status === 'graded' && is_numeric($score) && $pointsPossible > 0),
                        'tooltip' => $tooltip,
                    ];
                }
            }

            $studentTotalPercentage = $studentTotalPossible > 0 ? round(($studentTotalScore / $studentTotalPossible) * 100, 1) : 0;

            $student['processed_assignments'] = $processedAssignments;
            $student['total_score'] = number_format($studentTotalScore, 1);
            $student['total_possible'] = $studentTotalPossible;
            $student['total_percentage'] = $studentTotalPercentage;

            return $student;
        });

        return [
            'totalStudents' => $totalStudents,
            'totalAssignments' => $totalAssignments,
            'totalPointsAwarded' => number_format($totalPointsAwarded, 1),
            'totalPointsPossible' => $totalPointsPossible,
            'averagePercentage' => $averagePercentage,
            'assignmentGroups' => $assignmentGroups,
            'studentsWithScores' => $studentsWithScores,
        ];
    }

    private function calculatePercentagesReportData($studentsProgress)
    {
        $totalStudents = $studentsProgress->count();
        $totalAssignments = $studentsProgress->isNotEmpty() ? $studentsProgress->first()['assignments']->count() : 0;

        // Calculate graded assignments and percentages
        $totalGradedAssignments = 0;
        $totalPercentageSum = 0;
        $totalCompletionCount = 0;

        foreach ($studentsProgress as $student) {
            foreach ($student['assignments'] as $assignment) {
                if ($assignment['status'] === 'graded' &&
                    isset($assignment['score']) &&
                    isset($assignment['points_possible']) &&
                    $assignment['points_possible'] > 0) {
                    $totalGradedAssignments++;
                    $percentage = ($assignment['score'] / $assignment['points_possible']) * 100;
                    $totalPercentageSum += $percentage;
                }

                // Count completion (submitted or graded)
                if (in_array($assignment['status'], ['submitted', 'graded'])) {
                    $totalCompletionCount++;
                }
            }
        }

        $averagePercentage = $totalGradedAssignments > 0 ? round($totalPercentageSum / $totalGradedAssignments, 1) : 0;
        $totalPossibleCompletions = $totalStudents * $totalAssignments;
        $completionRate = $totalPossibleCompletions > 0 ? round(($totalCompletionCount / $totalPossibleCompletions) * 100, 1) : 0;

        // Get assignment groups for header
        $assignmentGroups = [];
        if ($studentsProgress->isNotEmpty()) {
            $assignmentGroups = $studentsProgress->first()['assignments']->groupBy('module_name')->map(function ($assignments) {
                return $assignments->map(function ($assignment) {
                    return [
                        'assignment_name' => $assignment['assignment_name'],
                        'assignment_id' => $assignment['assignment_id'],
                        'points_possible' => $assignment['points_possible'] ?? 0,
                    ];
                });
            });
        }

        // Process students with percentage information
        $studentsWithPercentages = $studentsProgress->map(function ($student) use ($assignmentGroups, $totalAssignments) {
            $processedAssignments = [];
            $studentPercentageSum = 0;
            $studentGradedCount = 0;

            foreach ($assignmentGroups as $moduleName => $assignments) {
                foreach ($assignments as $assignment) {
                    $studentAssignment = $student['assignments']->where('assignment_name', $assignment['assignment_name'])->first();

                    $score = $studentAssignment['score'] ?? 0;
                    $pointsPossible = $assignment['points_possible'];
                    $status = $studentAssignment['status'] ?? 'unsubmitted';
                    $color = $studentAssignment['color'] ?? 'bg-gray-200';
                    $displayValue = $studentAssignment['display_value'] ?? '-';

                    // Calculate tooltip information
                    $tooltip = $assignment['assignment_name'];
                    if ($status === 'graded' && is_numeric($score) && $pointsPossible > 0) {
                        $percentage = round(($score / $pointsPossible) * 100, 1);
                        $tooltip .= " - {$percentage}% ({$score}/{$pointsPossible} punten)";

                        // Add to student average calculation
                        $studentPercentageSum += $percentage;
                        $studentGradedCount++;
                    } else {
                        $tooltip .= " - {$displayValue}";
                    }

                    $processedAssignments[] = [
                        'assignment_name' => $assignment['assignment_name'],
                        'module_name' => $moduleName,
                        'color' => $color,
                        'display_value' => $displayValue,
                        'tooltip' => $tooltip,
                    ];
                }
            }

            $studentAveragePercentage = $studentGradedCount > 0 ? round($studentPercentageSum / $studentGradedCount, 1) : 0;

            $student['processed_assignments'] = $processedAssignments;
            $student['average_percentage'] = $studentAveragePercentage;
            $student['graded_count'] = $studentGradedCount;
            $student['total_assignments'] = $totalAssignments;

            return $student;
        });

        return [
            'totalStudents' => $totalStudents,
            'totalAssignments' => $totalAssignments,
            'totalGradedAssignments' => $totalGradedAssignments,
            'averagePercentage' => $averagePercentage,
            'completionRate' => $completionRate,
            'assignmentGroups' => $assignmentGroups,
            'studentsWithPercentages' => $studentsWithPercentages,
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
                $gradesData = $this->calculateGradesReportData($studentsProgress);
                return view('results.grades-report', array_merge($viewData, $gradesData));
            case 'percentages':
                $percentagesData = $this->calculatePercentagesReportData($studentsProgress);
                return view('results.percentages-report', array_merge($viewData, $percentagesData));
            case 'missing':
                $missingData = $this->calculateMissingReportData($studentsProgress);
                return view('results.missing-report', array_merge($viewData, $missingData));
            case 'attention':
                $studentsWithRisk = $this->calculateAttentionRisks($studentsProgress);
                $urgentCount = $studentsWithRisk->where('risk_level', 'urgent')->count();
                $highCount = $studentsWithRisk->where('risk_level', 'high')->count();
                $mediumCount = $studentsWithRisk->where('risk_level', 'medium')->count();

                return view('results.attention-report', [
                    'studentsProgress' => $studentsProgress,
                    'studentsWithRisk' => $studentsWithRisk,
                    'urgentCount' => $urgentCount,
                    'highCount' => $highCount,
                    'mediumCount' => $mediumCount,
                    'reportType' => $reportType
                ]);
            default:
                return view('results.basic-color-report', $viewData);
        }
    }
}
