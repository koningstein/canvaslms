{{-- resources/views/results/averages-report.blade.php --}}
@extends('layouts.layoutadmin')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Gemiddelden & Analyse Dashboard</h1>
        <p class="text-gray-600">Overzicht van prestaties, trends en analyses - focus op gemiddelden en inzichten</p>

        {{-- Report Type Indicator --}}
        <div class="mt-4 flex items-center gap-4">
            <span class="text-sm font-medium text-gray-700">Rapport type:</span>
            <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
                Gemiddelden & Analyse
            </span>

            {{-- Back to selection button --}}
            <a href="{{ route('results.select') }}" class="ml-auto px-4 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 print:hidden">
                Ander rapport genereren
            </a>
        </div>
    </div>

    {{-- Key Performance Indicators --}}
    <div class="mb-6 bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-6 gap-4 text-center">
            <div>
                <div class="text-xl font-bold text-purple-600">{{ $overallClassAverage ?? 0 }}%</div>
                <div class="text-xs text-gray-600">Klas Gemiddelde</div>
            </div>
            <div>
                <div class="text-xl font-bold text-green-600">{{ $highestStudentAverage ?? 0 }}%</div>
                <div class="text-xs text-gray-600">Beste Student</div>
            </div>
            <div>
                <div class="text-xl font-bold text-red-600">{{ $lowestStudentAverage ?? 0 }}%</div>
                <div class="text-xs text-gray-600">Laagste Student</div>
            </div>
            <div>
                <div class="text-xl font-bold text-green-600">{{ $studentsAbove75 ?? 0 }}</div>
                <div class="text-xs text-gray-600">Goed (≥75%)</div>
            </div>
            <div>
                <div class="text-xl font-bold text-yellow-600">{{ $studentsAbove55 ?? 0 }}</div>
                <div class="text-xs text-gray-600">Voldoende (55-74%)</div>
            </div>
            <div>
                <div class="text-xl font-bold text-orange-600">{{ $studentsBelow55 ?? 0 }}</div>
                <div class="text-xs text-gray-600">Onvoldoende (<55%)</div>
            </div>
        </div>
    </div>

    {{-- Charts Row - COMPACT DESIGN --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        {{-- Student Performance Chart --}}
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-sm font-semibold mb-3 text-gray-800">📊 Student Prestaties</h3>
            <div id="studentPerformanceChart" class="h-48"></div>
        </div>

        {{-- Performance Distribution --}}
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-sm font-semibold mb-3 text-gray-800">🎯 Prestatie Verdeling</h3>
            <div id="performanceDistributionChart" class="h-48"></div>
        </div>

        {{-- Module Performance Chart --}}
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-sm font-semibold mb-3 text-gray-800">📚 Module Prestaties</h3>
            <div id="modulePerformanceChart" class="h-48"></div>
        </div>
    </div>

    {{-- Analysis Row - TOP PERFORMERS EN AANDACHT NAAST ELKAAR --}}
    <div class="mb-6 grid grid-cols-2 gap-4">
        {{-- Top Performers --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-green-50 px-4 py-3 border-b">
                <h3 class="text-sm font-semibold text-green-800">🏆 Top Prestaties (≥75%)</h3>
            </div>
            <div class="p-4 max-h-64 overflow-y-auto">
                @if(isset($topPerformers) && count($topPerformers) > 0)
                    <div class="space-y-2">
                        @foreach($topPerformers as $index => $student)
                            <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                                <div class="flex items-center">
                                    <span class="w-6 h-6 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-bold mr-2">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $student['student_name'] ?? 'Onbekend' }}</div>
                                        <div class="text-xs text-gray-500">{{ $student['graded_count'] ?? 0 }}/{{ $student['total_assignments'] ?? 0 }} beoordeeld</div>
                                    </div>
                                </div>
                                <div class="text-sm font-bold text-green-600">{{ $student['average_percentage'] ?? 0 }}%</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-500 py-4">
                        Geen studenten met gemiddelde ≥75%
                    </div>
                @endif
            </div>
        </div>

        {{-- Students Needing Attention --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-red-50 px-4 py-3 border-b">
                <h3 class="text-sm font-semibold text-red-800">⚠️ Aandacht Nodig (<55%)</h3>
            </div>
            <div class="p-4 max-h-64 overflow-y-auto">
                @if(isset($lowPerformers) && count($lowPerformers) > 0)
                    <div class="space-y-2">
                        @foreach($lowPerformers as $student)
                            <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                                <div class="flex items-center">
                                    <span class="w-6 h-6 bg-red-100 text-red-800 rounded-full flex items-center justify-center text-xs font-bold mr-2">
                                        ⚠️
                                    </span>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $student['student_name'] ?? 'Onbekend' }}</div>
                                        <div class="text-xs text-gray-500">{{ $student['graded_count'] ?? 0 }}/{{ $student['total_assignments'] ?? 0 }} beoordeeld</div>
                                    </div>
                                </div>
                                <div class="text-sm font-bold text-red-600">{{ $student['average_percentage'] ?? 0 }}%</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-500 py-4">
                        🎉 Geen studenten onder 55%!
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Assignment Analysis - COMPACTER --}}
    <div class="mb-6">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-blue-50 px-4 py-3 border-b">
                <h3 class="text-sm font-semibold text-blue-800">📊 Opdracht Analyse</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Opdracht</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Module</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700">Gemiddelde</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700">Beoordeeld</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700">Status</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @if(isset($assignmentAnalysis))
                        @foreach($assignmentAnalysis->take(10) as $assignment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-xs font-medium text-gray-900">
                                    {{ Str::limit($assignment['assignment_name'] ?? 'Onbekend', 30) }}
                                </td>
                                <td class="px-4 py-2 text-xs text-gray-600">
                                    {{ $assignment['module_name'] ?? 'Onbekend' }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    @if(isset($assignment['average_percentage']) && $assignment['average_percentage'] !== null)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $assignment['average_color'] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $assignment['average_percentage'] }}%
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center text-xs text-gray-600">
                                    {{ $assignment['graded_count'] ?? 0 }}/{{ $assignment['total_responses'] ?? 0 }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    @if(isset($assignment['status_text']))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $assignment['status_color'] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $assignment['status_text'] }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                                Geen opdracht data beschikbaar
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Trend Analysis en Insights NAAST ELKAAR --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        {{-- Trend Analysis - Smaller --}}
        @if(isset($trendData) && count($trendData) > 0)
            <div class="bg-white p-4 rounded-lg shadow col-span-2">
                <h3 class="text-sm font-semibold mb-3 text-gray-800">📈 Trend Analyse</h3>
                <div id="trendChart" class="h-48"></div>
            </div>
        @endif

        {{-- Summary Insights - Compacter --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-purple-50 px-4 py-3 border-b">
                <h3 class="text-sm font-semibold text-purple-800">💡 Inzichten</h3>
            </div>
            <div class="p-4">
                <div class="space-y-3">
                    <div>
                        <h4 class="text-xs font-semibold text-gray-800 mb-2">🎯 Prestatie</h4>
                        <ul class="space-y-1 text-xs text-gray-600">
                            @if(isset($insights['performance']))
                                @foreach(array_slice($insights['performance'], 0, 3) as $insight)
                                    <li class="flex items-start">
                                        <span class="text-purple-500 mr-1">•</span>
                                        <span>{{ $insight }}</span>
                                    </li>
                                @endforeach
                            @else
                                <li class="text-gray-400">Geen data</li>
                            @endif
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-gray-800 mb-2">📚 Opdrachten</h4>
                        <ul class="space-y-1 text-xs text-gray-600">
                            @if(isset($insights['assignments']))
                                @foreach(array_slice($insights['assignments'], 0, 3) as $insight)
                                    <li class="flex items-start">
                                        <span class="text-purple-500 mr-1">•</span>
                                        <span>{{ $insight }}</span>
                                    </li>
                                @endforeach
                            @else
                                <li class="text-gray-400">Geen data</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Print/Export Options --}}
    <div class="mt-6 flex justify-end gap-2 print:hidden">
        <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
            📄 Afdrukken
        </button>
        <a href="{{ route('results.select') }}" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
            🔄 Nieuw rapport
        </a>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/averages-report.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/averages-charts.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts with data from PHP
            const averagesCharts = new AveragesCharts();

            // Chart data from PHP
            const chartData = @json($chartData ?? []);
            const trendData = @json($trendData ?? []);

            // Initialize all charts
            averagesCharts.init(chartData, trendData);

            // Handle window resize
            window.addEventListener('resize', function() {
                averagesCharts.handleResize();
            });

            // Cleanup on page unload
            window.addEventListener('beforeunload', function() {
                averagesCharts.destroyAll();
            });
        });
    </script>
@endpush
