{{-- resources/views/results/percentages-report.blade.php --}}
@extends('layouts.layoutadmin')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Percentages Rapport</h1>
        <p class="text-gray-600">Overzicht van behaalde percentages per opdracht</p>

        {{-- Report Type Indicator --}}
        <div class="mt-4 flex items-center gap-4">
            <span class="text-sm font-medium text-gray-700">Rapport type:</span>
            <span class="px-3 py-1 bg-teal-100 text-teal-800 rounded-full text-sm font-medium">
                Percentages
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
                <div class="text-xl font-bold text-purple-600">{{ $totalGradedAssignments }}</div>
                <div class="text-xs text-gray-600">Beoordeeld</div>
            </div>

            <div class="bg-white p-3 rounded-lg shadow border text-center">
                <div class="text-xl font-bold text-orange-600">{{ $averagePercentage }}%</div>
                <div class="text-xs text-gray-600">Gem. percentage</div>
            </div>

            <div class="bg-white p-3 rounded-lg shadow border text-center">
                <div class="text-xl font-bold text-gray-600">{{ $completionRate }}%</div>
                <div class="text-xs text-gray-600">Voltooiing</div>
            </div>
        </div>
    @endif

    {{-- Legend for Percentages Report --}}
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
                <span>Ingeleverd (nog niet beoordeeld)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-purple-200 rounded"></div>
                <span>Vrijgesteld</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-orange-200 rounded"></div>
                <span>Niet ingeleverd (0%)</span>
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
                <th class="px-2 py-3 border-l-2 border-teal-300 text-center text-sm font-semibold text-teal-700 bg-teal-50">
                    Gemiddelde
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
                <th class="px-2 py-1 border-l-2 border-teal-300 text-center text-xs font-medium text-teal-600 bg-teal-50">
                    Percentage
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($studentsWithPercentages as $student)
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
                        </td>
                    @endforeach
                    <td class="px-2 py-2 border-l-2 border-teal-300 text-sm text-center bg-teal-50">
                        <div class="font-bold text-teal-800">
                            {{ $student['average_percentage'] }}%
                        </div>
                        <div class="text-xs text-teal-600">
                            {{ $student['graded_count'] }}/{{ $student['total_assignments'] }} beoordeeld
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
