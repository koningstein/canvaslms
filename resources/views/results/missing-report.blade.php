{{-- resources/views/results/missing-report.blade.php --}}
@extends('layouts.layoutadmin')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Ontbrekende Opdrachten Rapport</h1>
        <p class="text-gray-600">Overzicht van niet ingeleverde en onvoldoende opdrachten per student</p>

        {{-- Report Type Indicator --}}
        <div class="mt-4 flex items-center gap-4">
            <span class="text-sm font-medium text-gray-700">Rapport type:</span>
            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                Ontbrekende Opdrachten
            </span>

            {{-- Back to selection button --}}
            <a href="{{ route('results.select') }}" class="ml-auto px-4 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
                Ander rapport genereren
            </a>
        </div>
    </div>

    {{-- Summary Statistics specifically for missing assignments --}}
    @if($studentsProgress->isNotEmpty())
        <div class="mb-6 grid grid-cols-5 gap-3">
            <div class="bg-white p-3 rounded-lg shadow border text-center">
                <div class="text-xl font-bold text-red-600">{{ $totalMissing }}</div>
                <div class="text-xs text-gray-600">Niet ingeleverd</div>
            </div>

            <div class="bg-white p-3 rounded-lg shadow border text-center">
                <div class="text-xl font-bold text-orange-600">{{ $totalInsufficient }}</div>
                <div class="text-xs text-gray-600">Onvoldoende</div>
            </div>

            <div class="bg-white p-3 rounded-lg shadow border text-center">
                <div class="text-xl font-bold text-purple-600">{{ $totalProblematic }}</div>
                <div class="text-xs text-gray-600">Totaal problematisch</div>
            </div>

            <div class="bg-white p-3 rounded-lg shadow border text-center">
                <div class="text-xl font-bold text-blue-600">{{ $studentsWithProblemsCount }}</div>
                <div class="text-xs text-gray-600">Studenten met problemen</div>
            </div>

            <div class="bg-white p-3 rounded-lg shadow border text-center">
                <div class="text-xl font-bold text-gray-600">{{ $problemRate }}%</div>
                <div class="text-xs text-gray-600">Probleem percentage</div>
            </div>
        </div>
    @endif

    {{-- Legend - AANGEPAST NAAR CONSISTENTE KLEUREN --}}
    <div class="mb-6 p-4 bg-red-50 rounded-lg border border-red-200">
        <h3 class="font-semibold mb-3 text-red-800">Let op: Dit rapport toont alleen problematische opdrachten</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-orange-200 rounded"></div>
                <span>Niet ingeleverd</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-200 rounded"></div>
                <span>Onvoldoende (&lt;55%)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-yellow-200 rounded"></div>
                <span>Te laat ingeleverd</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-white border border-gray-300 rounded"></div>
                <span>Voldoende/goed (verborgen)</span>
            </div>
        </div>
    </div>

    @if($studentsWithProblems->isEmpty())
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <div class="text-green-800 text-lg font-semibold mb-2">🎉 Geen problematische opdrachten gevonden!</div>
            <div class="text-green-700">Alle studenten hebben hun opdrachten voldoende afgerond.</div>
        </div>
    @else
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full border-collapse">
                <thead>
                <tr class="bg-red-50 border-b border-red-200">
                    <th class="px-3 py-3 border-r border-red-200 text-left text-sm font-semibold text-red-800 sticky left-0 bg-red-50 z-10 max-w-40">
                        Student ({{ $studentsWithProblems->count() }}/{{ $totalStudents }})
                    </th>
                    @foreach($assignmentGroups as $moduleName => $assignments)
                        <th class="px-2 py-3 border-r border-red-200 text-center text-sm font-semibold text-red-800" colspan="{{ $assignments->count() }}">
                            {{ $moduleName }}
                        </th>
                    @endforeach
                </tr>
                <tr class="bg-red-25 border-b border-red-200">
                    <th class="px-3 py-2 border-r border-red-200 text-left text-xs font-medium text-red-700 sticky left-0 bg-red-25 z-10 max-w-40">
                        &nbsp;
                    </th>
                    @foreach($assignmentGroups as $moduleName => $assignments)
                        @foreach($assignments as $assignment)
                            <th class="px-1 py-2 border-r border-red-200 text-center text-xs font-medium text-red-700 max-w-20" style="writing-mode: vertical-lr; text-orientation: mixed;">
                                <div class="truncate" title="{{ $assignment['assignment_name'] }}">
                                    {{ Str::limit($assignment['assignment_name'], 15) }}
                                </div>
                            </th>
                        @endforeach
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($studentsWithProblems as $student)
                    <tr class="border-b border-red-100 hover:bg-red-50">
                        <td class="px-3 py-3 border-r border-red-200 text-sm font-medium text-gray-900 sticky left-0 bg-white z-10 max-w-40">
                            <div class="truncate">
                                {{ $student['student_name'] }}
                                <span class="inline-block px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full ml-2">
                                    {{ $student['problem_count'] }} problemen
                                </span>
                            </div>
                            <div class="text-xs text-gray-500">
                                @if(isset($student['sis_user_id']) && $student['sis_user_id'])
                                    {{ $student['sis_user_id'] }}
                                @else
                                    ID: {{ $student['student_id'] }}
                                @endif
                            </div>
                        </td>
                        @foreach($student['processed_assignments'] as $assignment)
                            <td class="px-2 py-3 border-r border-red-200 text-xs text-center {{ $assignment['cell_color'] }} max-w-20"
                                title="{{ $assignment['tooltip'] }}">
                                <div class="font-medium">
                                    {{ $assignment['display_value'] }}
                                </div>
                                @if($assignment['show_late_icon'])
                                    <div class="text-xs text-yellow-800">⏰</div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

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
            table { font-size: 10px !important; }
            th, td { padding: 2px 4px !important; }
            .sticky { position: static !important; }
            body { font-size: 12px !important; }
            .bg-white { background: white !important; }
            .shadow { box-shadow: none !important; }
        }
    </style>
@endsection
