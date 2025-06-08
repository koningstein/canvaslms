@extends('layouts.layoutadmin')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Basis Kleur Overzicht</h1>
        <p class="text-gray-600">Overzicht van de voortgang van studenten met kleurcodering</p>

        {{-- Report Type Indicator --}}
        <div class="mt-4 flex items-center gap-4">
            <span class="text-sm font-medium text-gray-700">Rapport type:</span>
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                Basis Kleur Overzicht
            </span>

            {{-- Back to selection button --}}
            <a href="{{ route('results.select') }}" class="ml-auto px-4 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
                Ander rapport genereren
            </a>
        </div>
    </div>

    {{-- Summary Statistics --}}
    @if($studentsProgress->isNotEmpty())
        <div class="mb-6 grid grid-cols-4 gap-4">
            @php
                $totalStudents = $studentsProgress->count();
                $totalAssignments = $studentsProgress->first()['assignments']->count();
                $totalSubmissions = $studentsProgress->sum(function($student) {
                    return $student['assignments']->whereIn('status', ['graded', 'submitted'])->count();
                });
                $totalPossible = $totalStudents * $totalAssignments;
                $completionRate = $totalPossible > 0 ? round(($totalSubmissions / $totalPossible) * 100, 1) : 0;
            @endphp

            <div class="bg-white p-4 rounded-lg shadow border">
                <div class="text-2xl font-bold text-blue-600">{{ $totalStudents }}</div>
                <div class="text-sm text-gray-600">Studenten</div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow border">
                <div class="text-2xl font-bold text-green-600">{{ $totalAssignments }}</div>
                <div class="text-sm text-gray-600">Opdrachten</div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow border">
                <div class="text-2xl font-bold text-purple-600">{{ $totalSubmissions }}</div>
                <div class="text-sm text-gray-600">Inleveringen</div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow border">
                <div class="text-2xl font-bold text-orange-600">{{ $completionRate }}%</div>
                <div class="text-sm text-gray-600">Voortgang</div>
            </div>
        </div>
    @endif

    {{-- Legend for Basic Color Report --}}
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <h3 class="font-semibold mb-3">Legenda:</h3>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-400 rounded"></div>
                <span>Goed (≥75%)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-yellow-400 rounded"></div>
                <span>Voldoende (55-74%)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-orange-400 rounded"></div>
                <span>Onvoldoende (<55%)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-blue-400 rounded"></div>
                <span>Ingeleverd</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-300 rounded"></div>
                <span>Niet ingeleverd</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-gray-200 rounded"></div>
                <span>Geen inlevering</span>
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
                @if($studentsProgress->isNotEmpty())
                    @foreach($studentsProgress->first()['assignments']->groupBy('module_name') as $moduleName => $assignments)
                        <th class="px-2 py-3 border-r border-gray-200 text-center text-sm font-semibold text-gray-700" colspan="{{ $assignments->count() }}">
                            {{ $moduleName }}
                        </th>
                    @endforeach
                @endif
            </tr>
            <tr class="bg-gray-25 border-b border-gray-200">
                <th class="px-2 py-1 border-r border-gray-200 text-left text-xs font-medium text-gray-600 sticky left-0 bg-gray-25 z-10 max-w-40">
                    &nbsp;
                </th>
                @if($studentsProgress->isNotEmpty())
                    @foreach($studentsProgress->first()['assignments']->groupBy('module_name') as $moduleName => $assignments)
                        @foreach($assignments as $assignment)
                            <th class="px-1 py-1 border-r border-gray-200 text-center text-xs font-medium text-gray-600 max-w-16" style="writing-mode: vertical-lr; text-orientation: mixed;">
                                <div class="truncate" title="{{ $assignment['assignment_name'] }}">
                                    {{ Str::limit($assignment['assignment_name'], 15) }}
                                </div>
                            </th>
                        @endforeach
                    @endforeach
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($studentsProgress as $student)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-2 py-2 border-r border-gray-200 text-sm font-medium text-gray-900 sticky left-0 bg-white z-10 max-w-40">
                        <div class="truncate">{{ $student['student_name'] }} ({{ $student['student_id'] }})</div>
                    </td>
                    @if($studentsProgress->isNotEmpty())
                        @foreach($studentsProgress->first()['assignments']->groupBy('module_name') as $moduleName => $assignments)
                            @foreach($assignments as $assignment)
                                @php
                                    $studentAssignment = $student['assignments']->where('assignment_name', $assignment['assignment_name'])->first();
                                @endphp
                                <td class="px-1 py-2 border-r border-gray-200 text-xs text-center {{ $studentAssignment['color'] ?? 'bg-gray-100' }} max-w-16"
                                    title="{{ $assignment['assignment_name'] }} - {{ $studentAssignment['display_value'] ?? 'Geen data' }}">
                                    &nbsp;
                                </td>
                            @endforeach
                        @endforeach
                    @endif
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
            table { font-size: 10px !important; }
            th, td { padding: 2px 4px !important; }
            .sticky { position: static !important; }
            body { font-size: 12px !important; }
            .bg-white { background: white !important; }
            .shadow { box-shadow: none !important; }
        }
    </style>
@endsection
