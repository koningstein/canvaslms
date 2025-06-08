{{-- resources/views/results/attention-report.blade.php --}}
@extends('layouts.layoutadmin')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Aandachtspunten Rapport</h1>
        <p class="text-gray-600">Studenten gerangschikt op risico niveau</p>

        <div class="mt-4 flex items-center gap-4">
            <span class="text-sm font-medium text-gray-700">Rapport type:</span>
            <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-medium">
                Aandachtspunten
            </span>
            <a href="{{ route('results.select') }}" class="ml-auto px-4 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
                Ander rapport genereren
            </a>
        </div>
    </div>

    @php
        $studentsWithRisk = collect();

        foreach($studentsProgress as $student) {
            $missing = 0;
            $insufficient = 0;
            $needsGrading = 0;
            $needsReview = 0;
            $late = 0;

            foreach($student['assignments'] as $assignment) {
                if (isset($assignment['excused']) && $assignment['excused']) continue;

                $submissionTypes = $assignment['submission_types'] ?? [];
                $isNonSubmittable = empty($submissionTypes) || in_array('none', $submissionTypes);
                $hasGrade = ($assignment['status'] === 'graded') ||
                    ($assignment['score'] !== null && $assignment['score'] > 0) ||
                    (!empty($assignment['grade']) && $assignment['grade'] !== 'null');

                // Count missing (submittable but not submitted)
                if (!$isNonSubmittable && $assignment['status'] === 'unsubmitted') {
                    $missing++;
                }

                // Count insufficient grades
                if ($assignment['status'] === 'graded' &&
                    isset($assignment['score']) &&
                    isset($assignment['points_possible']) &&
                    $assignment['points_possible'] > 0 &&
                    ($assignment['score'] / $assignment['points_possible'] * 100) < 55) {
                    $insufficient++;
                }

                // Count non-submittable assignments needing grading
                if ($isNonSubmittable && !$hasGrade && ($assignment['points_possible'] ?? 0) > 0) {
                    $needsGrading++;
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
                        }
                    }
                }
            }

            // Calculate risk score
            $riskScore = ($missing * 5) + ($insufficient * 4) + ($needsGrading * 2) + ($late * 1) + ($needsReview * 1);

            if ($riskScore > 0) {
                $riskLevel = $riskScore >= 8 ? 'urgent' : ($riskScore >= 4 ? 'high' : 'medium');

                $studentsWithRisk->push([
                    'student' => $student,
                    'risk_score' => $riskScore,
                    'risk_level' => $riskLevel,
                    'missing' => $missing,
                    'insufficient' => $insufficient,
                    'needs_grading' => $needsGrading,
                    'needs_review' => $needsReview,
                    'late' => $late
                ]);
            }
        }

        $studentsWithRisk = $studentsWithRisk->sortByDesc('risk_score');

        $urgentCount = $studentsWithRisk->where('risk_level', 'urgent')->count();
        $highCount = $studentsWithRisk->where('risk_level', 'high')->count();
        $mediumCount = $studentsWithRisk->where('risk_level', 'medium')->count();
    @endphp

    <div class="mb-6 grid grid-cols-6 gap-3">
        <div class="bg-white p-3 rounded-lg shadow border text-center">
            <div class="text-xl font-bold text-red-600">{{ $urgentCount }}</div>
            <div class="text-xs text-gray-600">Urgent</div>
        </div>
        <div class="bg-white p-3 rounded-lg shadow border text-center">
            <div class="text-xl font-bold text-orange-600">{{ $highCount }}</div>
            <div class="text-xs text-gray-600">Hoog risico</div>
        </div>
        <div class="bg-white p-3 rounded-lg shadow border text-center">
            <div class="text-xl font-bold text-yellow-600">{{ $mediumCount }}</div>
            <div class="text-xs text-gray-600">Gemiddeld risico</div>
        </div>
        <div class="bg-white p-3 rounded-lg shadow border text-center">
            <div class="text-xl font-bold text-blue-600">{{ $studentsWithRisk->sum('needs_review') }}</div>
            <div class="text-xs text-gray-600">Te beoordelen</div>
        </div>
        <div class="bg-white p-3 rounded-lg shadow border text-center">
            <div class="text-xl font-bold text-purple-600">{{ $studentsWithRisk->sum('needs_grading') }}</div>
            <div class="text-xs text-gray-600">Nog geen cijfer</div>
        </div>
        <div class="bg-white p-3 rounded-lg shadow border text-center">
            <div class="text-xl font-bold text-gray-600">{{ $studentsWithRisk->count() }}</div>
            <div class="text-xs text-gray-600">Totaal aandacht</div>
        </div>
    </div>

    <div class="mb-6 p-3 bg-orange-50 rounded-lg border border-orange-200">
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-500 rounded"></div>
                <span><strong>Urgent (≥8)</strong> - Directe actie</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-orange-500 rounded"></div>
                <span><strong>Hoog (4-7)</strong> - Deze week contact</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                <span><strong>Gemiddeld (1-3)</strong> - Monitor</span>
            </div>
        </div>
    </div>

    @if($studentsWithRisk->isEmpty())
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <div class="text-green-800 text-lg font-semibold mb-2">🎉 Geen studenten hebben aandachtspunten!</div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Student</th>
                    <th class="px-3 py-3 text-center text-sm font-semibold text-gray-700">Risico</th>
                    <th class="px-3 py-3 text-center text-sm font-semibold text-gray-700">Ontbreekt</th>
                    <th class="px-3 py-3 text-center text-sm font-semibold text-gray-700">Onvold.</th>
                    <th class="px-3 py-3 text-center text-sm font-semibold text-gray-700">Te laat</th>
                    <th class="px-3 py-3 text-center text-sm font-semibold text-gray-700">Te beoordelen</th>
                    <th class="px-3 py-3 text-center text-sm font-semibold text-gray-700">Nog geen cijfer</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Specifieke Opdrachten</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @foreach($studentsWithRisk as $studentRisk)
                    @php
                        $student = $studentRisk['student'];
                        $riskLevel = $studentRisk['risk_level'];
                        $rowColor = $riskLevel === 'urgent' ? 'bg-red-50' :
                                   ($riskLevel === 'high' ? 'bg-orange-50' : 'bg-yellow-50');
                    @endphp

                    <tr class="{{ $rowColor }} hover:bg-opacity-75">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $student['student_name'] }}</div>
                            <div class="text-xs text-gray-500">
                                @if(isset($student['sis_user_id']) && $student['sis_user_id'])
                                    {{ $student['sis_user_id'] }}
                                @else
                                    ID: {{ $student['student_id'] }}
                                @endif
                            </div>
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($riskLevel === 'urgent')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-200 text-red-800">
                                        🚨 {{ $studentRisk['risk_score'] }}
                                    </span>
                            @elseif($riskLevel === 'high')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-200 text-orange-800">
                                        ⚠️ {{ $studentRisk['risk_score'] }}
                                    </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-200 text-yellow-800">
                                        📋 {{ $studentRisk['risk_score'] }}
                                    </span>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($studentRisk['missing'] > 0)
                                <span class="inline-flex items-center justify-center w-6 h-6 bg-red-200 text-red-800 rounded-full text-xs font-bold">
                                        {{ $studentRisk['missing'] }}
                                    </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($studentRisk['insufficient'] > 0)
                                <span class="inline-flex items-center justify-center w-6 h-6 bg-orange-200 text-orange-800 rounded-full text-xs font-bold">
                                        {{ $studentRisk['insufficient'] }}
                                    </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($studentRisk['late'] > 0)
                                <span class="inline-flex items-center justify-center w-6 h-6 bg-yellow-200 text-yellow-800 rounded-full text-xs font-bold">
                                        {{ $studentRisk['late'] }}
                                    </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($studentRisk['needs_review'] > 0)
                                <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-200 text-blue-800 rounded-full text-xs font-bold">
                                        {{ $studentRisk['needs_review'] }}
                                    </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($studentRisk['needs_grading'] > 0)
                                <span class="inline-flex items-center justify-center w-6 h-6 bg-purple-200 text-purple-800 rounded-full text-xs font-bold">
                                        {{ $studentRisk['needs_grading'] }}
                                    </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <div class="text-xs space-y-1 max-w-md">
                                @php
                                    $missingList = [];
                                    $insufficientList = [];
                                    $needsGradingList = [];
                                    $lateList = [];

                                    foreach($student['assignments'] as $assignment) {
                                        if (isset($assignment['excused']) && $assignment['excused']) continue;

                                        $submissionTypes = $assignment['submission_types'] ?? [];
                                        $isNonSubmittable = empty($submissionTypes) || in_array('none', $submissionTypes);

                                        // Missing assignments
                                        if (!$isNonSubmittable && $assignment['status'] === 'unsubmitted') {
                                            $missingList[] = $assignment['assignment_name'];
                                        }

                                        // Insufficient grades
                                        if ($assignment['status'] === 'graded' &&
                                            isset($assignment['score']) &&
                                            isset($assignment['points_possible']) &&
                                            $assignment['points_possible'] > 0 &&
                                            ($assignment['score'] / $assignment['points_possible'] * 100) < 55) {
                                            $percentage = round(($assignment['score'] / $assignment['points_possible']) * 100);
                                            $insufficientList[] = $assignment['assignment_name'] . ' (' . $percentage . '%)';
                                        }

                                        // Late submissions (only problematic ones)
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
                                                    $lateList[] = $assignment['assignment_name'];
                                                }
                                            }
                                        }

                                        // Needs grading
                                        $hasGrade = ($assignment['status'] === 'graded') ||
                                            ($assignment['score'] !== null && $assignment['score'] > 0) ||
                                            (!empty($assignment['grade']) && $assignment['grade'] !== 'null');

                                        if ($isNonSubmittable && !$hasGrade && ($assignment['points_possible'] ?? 0) > 0) {
                                            $needsGradingList[] = $assignment['assignment_name'];
                                        }
                                    }
                                @endphp

                                @if(count($missingList) > 0)
                                    <div class="text-red-700">
                                        <strong>Ontbreekt:</strong>
                                        {{ implode(', ', array_slice($missingList, 0, 3)) }}
                                        @if(count($missingList) > 3)
                                            <span class="text-red-500"> +{{ count($missingList) - 3 }} meer</span>
                                        @endif
                                    </div>
                                @endif

                                @if(count($insufficientList) > 0)
                                    <div class="text-orange-700">
                                        <strong>Onvoldoende:</strong>
                                        {{ implode(', ', array_slice($insufficientList, 0, 2)) }}
                                        @if(count($insufficientList) > 2)
                                            <span class="text-orange-500"> +{{ count($insufficientList) - 2 }} meer</span>
                                        @endif
                                    </div>
                                @endif

                                @if(count($lateList) > 0)
                                    <div class="text-yellow-700">
                                        <strong>Te laat & problematisch:</strong>
                                        {{ implode(', ', array_slice($lateList, 0, 2)) }}
                                        @if(count($lateList) > 2)
                                            <span class="text-yellow-500"> +{{ count($lateList) - 2 }} meer</span>
                                        @endif
                                    </div>
                                @endif

                                @if(count($needsGradingList) > 0)
                                    <div class="text-purple-700">
                                        <strong>Nog geen cijfer:</strong>
                                        {{ implode(', ', array_slice($needsGradingList, 0, 2)) }}
                                        @if(count($needsGradingList) > 2)
                                            <span class="text-purple-500"> +{{ count($needsGradingList) - 2 }} meer</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="mt-6 flex justify-end gap-2">
        <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
            Afdrukken
        </button>
        <a href="{{ route('results.select') }}" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
            Nieuw rapport
        </a>
    </div>

    <style>
        @media print {
            .sidebar, nav, .no-print { display: none !important; }
            .overflow-x-auto { overflow: visible !important; }
            table { font-size: 10px !important; }
            th, td { padding: 2px 4px !important; }
            body { font-size: 12px !important; }
            .bg-white { background: white !important; }
            .shadow { box-shadow: none !important; }
        }
    </style>
@endsection
