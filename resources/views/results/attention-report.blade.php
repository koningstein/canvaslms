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

    {{-- Legend - AANGEPAST NAAR CONSISTENTE KLEUREN --}}
    <div class="mb-6 p-3 bg-orange-50 rounded-lg border border-orange-200">
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-200 rounded"></div>
                <span><strong>Urgent (≥5)</strong> - Meerdere echte problemen</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-200 rounded"></div>
                <span><strong>Hoog (3-4)</strong> - Enkele problemen, aanspreken</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-yellow-200 rounded"></div>
                <span><strong>Gemiddeld (1-2)</strong> - Administratief/nakijken</span>
            </div>
        </div>
        <div class="mt-3 text-xs text-gray-600">
            <strong>Scoreverdeling:</strong> Ontbreekt (3 punten), Onvoldoende (3 of 1 punt*), Te laat/Te beoordelen/Nog geen cijfer (1 punt)<br>
            <strong>Slimme scoring:</strong> *Onvoldoende niet-inleverbare opdrachten in groepen met inleveringen krijgen slechts 1 punt
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
                                   ($riskLevel === 'high' ? 'bg-red-50' : 'bg-yellow-50');
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
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        🚨 {{ $studentRisk['risk_score'] }}
                                    </span>
                            @elseif($riskLevel === 'high')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ⚠️ {{ $studentRisk['risk_score'] }}
                                    </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        📋 {{ $studentRisk['risk_score'] }}
                                    </span>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($studentRisk['missing'] > 0)
                                <span class="inline-flex items-center justify-center w-6 h-6 bg-orange-200 text-red-800 rounded-full text-xs font-bold">
                                        {{ $studentRisk['missing'] }}
                                    </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($studentRisk['insufficient'] > 0)
                                <span class="inline-flex items-center justify-center w-6 h-6 bg-red-200 text-red-800 rounded-full text-xs font-bold">
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
                                @if(count($studentRisk['missing_list']) > 0)
                                    <div class="text-red-700">
                                        <strong>Ontbreekt:</strong>
                                        {{ implode(', ', array_slice($studentRisk['missing_list'], 0, 3)) }}
                                        @if(count($studentRisk['missing_list']) > 3)
                                            <span class="text-red-500"> +{{ count($studentRisk['missing_list']) - 3 }} meer</span>
                                        @endif
                                    </div>
                                @endif

                                @if(count($studentRisk['insufficient_list']) > 0)
                                    <div class="text-red-700">
                                        <strong>Onvoldoende:</strong>
                                        {{ implode(', ', array_slice($studentRisk['insufficient_list'], 0, 2)) }}
                                        @if(count($studentRisk['insufficient_list']) > 2)
                                            <span class="text-red-500"> +{{ count($studentRisk['insufficient_list']) - 2 }} meer</span>
                                        @endif
                                    </div>
                                @endif

                                @if(count($studentRisk['late_list']) > 0)
                                    <div class="text-yellow-700">
                                        <strong>Te laat & problematisch:</strong>
                                        {{ implode(', ', array_slice($studentRisk['late_list'], 0, 2)) }}
                                        @if(count($studentRisk['late_list']) > 2)
                                            <span class="text-yellow-500"> +{{ count($studentRisk['late_list']) - 2 }} meer</span>
                                        @endif
                                    </div>
                                @endif

                                @if(count($studentRisk['needs_grading_list']) > 0)
                                    <div class="text-purple-700">
                                        <strong>Nog geen cijfer:</strong>
                                        {{ implode(', ', array_slice($studentRisk['needs_grading_list'], 0, 2)) }}
                                        @if(count($studentRisk['needs_grading_list']) > 2)
                                            <span class="text-purple-500"> +{{ count($studentRisk['needs_grading_list']) - 2 }} meer</span>
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
