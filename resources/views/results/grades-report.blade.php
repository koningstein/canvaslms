{{-- resources/views/results/grades-report.blade.php --}}
@extends('layouts.layoutadmin')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Numerieke Cijfers Rapport</h1>
        <p class="text-gray-600">Overzicht van behaalde punten en cijfers per opdracht</p>

        {{-- Report Type Indicator --}}
        <div class="mt-4 flex items-center gap-4">
            <span class="text-sm font-medium text-gray-700">Rapport type:</span>
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                Numerieke Cijfers
            </span>

            {{-- Back to selection button --}}
            <a href="{{ route('results.select') }}" class="ml-auto px-4 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
                Ander rapport genereren
            </a>
        </div>
    </div>

    {{-- Summary Statistics --}}
    @if($studentsProgress->isNotEmpty())
        <div class="mb-6 grid grid-cols-5 gap-3">
            <div class="bg-white p-3 rounded-lg shadow border text-center">
                <div class="text-xl font-bold text-blue-600">{{ $totalStudents }}</div>
                <div class="text-xs text-gray-600">Studenten</div>
            </div>

            <div class="bg-white p-3 rounded-lg shadow border text-center">
                <div class="text-xl font-bold text-green-600">{{ $totalAssignments }}</div>
                <div class="text-xs text-gray-600">Opdrachten</div>
            </div>

            <div class="bg-white p-3 rounded-lg shadow border text-center">
                <div class="text-xl font-bold text-purple-600">{{ $totalPointsAwarded }}</div>
                <div class="text-xs text-gray-600">Punten behaald</div>
            </div>

            <div class="bg-white p-3 rounded-lg shadow border text-center">
                <div class="text-xl font-bold text-orange-600">{{ $totalPointsPossible }}</div>
                <div class="text-xs text-gray-600">Punten mogelijk</div>
            </div>

            <div class="bg-white p-3 rounded-lg shadow border text-center">
                <div class="text-xl font-bold text-gray-600">{{ $averagePercentage }}%</div>
                <div class="text-xs text-gray-600">Gemiddeld percentage</div>
            </div>
        </div>
    @endif

    {{-- Legend for Grades Report - IDENTIEK AAN PERCENTAGE RAPPORT --}}
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <h3 class="font-semibold mb-3">Legenda:</h3>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-200 rounded"></div>
                <span>Goed (≥75%)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-yellow-200 rounded"></div>
                <span>Voldoende (55-74%)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-200 rounded"></div>
                <span>Onvoldoende (&lt;55%)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-blue-200 rounded"></div>
                <span>Ingeleverd (niet beoordeeld)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-orange-200 rounded"></div>
                <span>Niet ingeleverd</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-purple-200 rounded"></div>
                <span>Vrijgesteld</span>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse">
            <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="px-2 py-2 border-r border-gray-200 text-left text-sm font-semibold text-gray-700 sticky left-0 bg-gray-50 z-10 max-w-40">
                    Student
                </th>
                @foreach($assignmentGroups as $moduleName => $assignments)
                    <th class="px-2 py-3 border-r border-gray-200 text-center text-sm font-semibold text-gray-700" colspan="{{ count($assignments) }}">
                        {{ $moduleName }}
                    </th>
                @endforeach
                <th class="px-2 py-3 border-l-2 border-blue-300 text-center text-sm font-semibold text-blue-700 bg-blue-50">
                    Totaal
                </th>
            </tr>
            <tr class="bg-gray-25 border-b border-gray-200">
                <th class="px-2 py-1 border-r border-gray-200 text-left text-xs font-medium text-gray-600 sticky left-0 bg-gray-25 z-10 max-w-40">
                    &nbsp;
                </th>
                @foreach($assignmentGroups as $moduleName => $assignments)
                    @foreach($assignments as $assignment)
                        <th class="px-1 py-1 border-r border-gray-200 text-center text-xs font-medium text-gray-600 max-w-20" style="writing-mode: vertical-lr; text-orientation: mixed;">
                            <div class="truncate" title="{{ $assignment['assignment_name'] }} ({{ $assignment['points_possible'] }} punten)">
                                {{ Str::limit($assignment['assignment_name'], 15) }}
                            </div>
                            <div class="text-xs text-gray-400 mt-1" style="writing-mode: horizontal-tb;">
                                ({{ $assignment['points_possible'] }}p)
                            </div>
                        </th>
                    @endforeach
                @endforeach
                <th class="px-2 py-1 border-l-2 border-blue-300 text-center text-xs font-medium text-blue-600 bg-blue-50">
                    Behaald/Mogelijk
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($studentsWithScores as $student)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-2 py-2 border-r border-gray-200 text-sm font-medium text-gray-900 sticky left-0 bg-white z-10 max-w-40">
                        <div class="truncate">{{ $student['student_name'] }}</div>
                        <div class="text-xs text-gray-500">
                            @if(isset($student['sis_user_id']) && $student['sis_user_id'])
                                {{ $student['sis_user_id'] }}
                            @else
                                ID: {{ $student['student_id'] }}
                            @endif
                        </div>
                    </td>
                    @foreach($student['processed_assignments'] as $assignment)
                        <td class="px-1 py-2 border-r border-gray-200 text-xs text-center {{ $assignment['color'] }} max-w-20"
                            title="{{ $assignment['tooltip'] }}">
                            <div class="font-medium">
                                {{ $assignment['display_value'] }}
                            </div>
                            @if($assignment['show_points_possible'])
                                <div class="text-xs text-gray-600">
                                    /{{ $assignment['points_possible'] }}
                                </div>
                            @endif
                        </td>
                    @endforeach
                    <td class="px-2 py-2 border-l-2 border-blue-300 text-sm text-center bg-blue-50">
                        <div class="font-bold text-blue-800">
                            {{ $student['total_score'] }}/{{ $student['total_possible'] }}
                        </div>
                        <div class="text-xs text-blue-600">
                            {{ $student['total_percentage'] }}%
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Print/Export Options --}}
    <div class="mt-6 flex justify-end gap-2">
        <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
            Afdrukken
        </button>
        <a href="{{ route('results.select') }}" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
            Nieuw rapport
        </a>
    </div>

    {{-- Print Styles --}}
    <style>
        @media print {
            .sidebar, nav, .no-print { display: none !important; }
            .overflow-x-auto { overflow: visible !important; }
            table { font-size: 9px !important; }
            th, td { padding: 1px 2px !important; }
            .sticky { position: static !important; }
            body { font-size: 12px !important; }
            .bg-white { background: white !important; }
            .shadow { box-shadow: none !important; }
        }
    </style>
@endsection
