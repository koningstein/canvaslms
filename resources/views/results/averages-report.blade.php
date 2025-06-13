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
            <a href="{{ route('results.select') }}" class="ml-auto px-4 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
                Ander rapport genereren
            </a>
        </div>
    </div>

    {{-- Key Performance Indicators --}}
    <div class="mb-8 grid grid-cols-2 md:grid-cols-6 gap-4">
        <div class="bg-white p-4 rounded-lg shadow border text-center">
            <div class="text-2xl font-bold text-purple-600">{{ $overallClassAverage ?? 0 }}%</div>
            <div class="text-sm text-gray-600">Klas Gemiddelde</div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border text-center">
            <div class="text-2xl font-bold text-green-600">{{ $highestStudentAverage ?? 0 }}%</div>
            <div class="text-sm text-gray-600">Beste Student</div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border text-center">
            <div class="text-2xl font-bold text-red-600">{{ $lowestStudentAverage ?? 0 }}%</div>
            <div class="text-sm text-gray-600">Laagste Student</div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $studentsAbove75 ?? 0 }}</div>
            <div class="text-sm text-gray-600">Goed (≥75%)</div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ $studentsAbove55 ?? 0 }}</div>
            <div class="text-sm text-gray-600">Voldoende (55-74%)</div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border text-center">
            <div class="text-2xl font-bold text-orange-600">{{ $studentsBelow55 ?? 0 }}</div>
            <div class="text-sm text-gray-600">Onvoldoende (<55%)</div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Student Performance Chart --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Student Prestaties</h3>
            <div id="studentPerformanceChart" class="h-64"></div>
        </div>

        {{-- Performance Distribution --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Prestatie Verdeling</h3>
            <div id="performanceDistributionChart" class="h-64"></div>
        </div>
    </div>

    {{-- Module Performance Chart --}}
    <div class="mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Module/Opdracht Gemiddelden</h3>
            <div id="modulePerformanceChart" class="h-80"></div>
        </div>
    </div>

    {{-- Analysis Tables Row --}}
    <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top Performers --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-green-50 px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-green-800">🏆 Top Prestaties (≥75%)</h3>
            </div>
            <div class="p-6">
                @if(isset($topPerformers) && count($topPerformers) > 0)
                    <div class="space-y-3">
                        @foreach($topPerformers as $index => $student)
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                <div class="flex items-center">
                                    <span class="w-8 h-8 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-sm font-bold mr-3">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $student['student_name'] ?? 'Onbekend' }}</div>
                                        <div class="text-sm text-gray-500">{{ $student['graded_count'] ?? 0 }}/{{ $student['total_assignments'] ?? 0 }} beoordeeld</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-green-600">{{ $student['average_percentage'] ?? 0 }}%</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-500 py-8">
                        Geen studenten met gemiddelde ≥75%
                    </div>
                @endif
            </div>
        </div>

        {{-- Students Needing Attention --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-red-50 px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-red-800">⚠️ Aandacht Nodig (<55%)</h3>
            </div>
            <div class="p-6">
                @if(isset($lowPerformers) && count($lowPerformers) > 0)
                    <div class="space-y-3">
                        @foreach($lowPerformers as $student)
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                <div class="flex items-center">
                                    <span class="w-8 h-8 bg-red-100 text-red-800 rounded-full flex items-center justify-center text-sm font-bold mr-3">
                                        ⚠️
                                    </span>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $student['student_name'] ?? 'Onbekend' }}</div>
                                        <div class="text-sm text-gray-500">{{ $student['graded_count'] ?? 0 }}/{{ $student['total_assignments'] ?? 0 }} beoordeeld</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-red-600">{{ $student['average_percentage'] ?? 0 }}%</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-500 py-8">
                        🎉 Geen studenten onder 55%!
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Assignment Analysis - SIMPLIFIED --}}
    <div class="mb-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-blue-50 px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-blue-800">📊 Opdracht Analyse</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Opdracht</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Module</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Gemiddelde</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Beoordeeld</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Status</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @if(isset($assignmentAnalysis))
                        @foreach($assignmentAnalysis as $assignment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $assignment['assignment_name'] ?? 'Onbekend' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $assignment['module_name'] ?? 'Onbekend' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if(isset($assignment['average_percentage']) && $assignment['average_percentage'] !== null)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium {{ $assignment['average_color'] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $assignment['average_percentage'] }}%
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-600">
                                    {{ $assignment['graded_count'] ?? 0 }}/{{ $assignment['total_responses'] ?? 0 }}
                                    @if(isset($assignment['completion_percentage']))
                                        <div class="text-xs text-gray-400">
                                            ({{ $assignment['completion_percentage'] }}%)
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
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
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                Geen opdracht data beschikbaar
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Trend Analysis --}}
    @if(isset($trendData) && count($trendData) > 0)
        <div class="mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">📈 Trend Analyse (Inlever Tijden)</h3>
                <div id="trendChart" class="h-64"></div>
            </div>
        </div>
    @endif

    {{-- Summary Insights --}}
    <div class="mb-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-purple-50 px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-purple-800">💡 Inzichten & Aanbevelingen</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-3">🎯 Prestatie Inzichten</h4>
                        <ul class="space-y-2 text-sm text-gray-600">
                            @if(isset($insights['performance']))
                                @foreach($insights['performance'] as $insight)
                                    <li class="flex items-start">
                                        <span class="text-purple-500 mr-2">•</span>
                                        {{ $insight }}
                                    </li>
                                @endforeach
                            @else
                                <li class="text-gray-400">Geen prestatie inzichten beschikbaar</li>
                            @endif
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-3">📚 Opdracht Inzichten</h4>
                        <ul class="space-y-2 text-sm text-gray-600">
                            @if(isset($insights['assignments']))
                                @foreach($insights['assignments'] as $insight)
                                    <li class="flex items-start">
                                        <span class="text-purple-500 mr-2">•</span>
                                        {{ $insight }}
                                    </li>
                                @endforeach
                            @else
                                <li class="text-gray-400">Geen opdracht inzichten beschikbaar</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
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

    {{-- ApexCharts Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(isset($chartData))
            // Student Performance Bar Chart
            @if(isset($chartData['studentNames']) && count($chartData['studentNames']) > 0)
            const studentPerformanceOptions = {
                series: [{
                    name: 'Gemiddelde %',
                    data: @json($chartData['studentPerformances'] ?? [])
                }],
                chart: {
                    type: 'bar',
                    height: 250,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        distributed: true,
                        dataLabels: { position: 'center' }
                    }
                },
                colors: @json($chartData['studentColors'] ?? []),
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val + '%';
                    },
                    style: { fontSize: '12px', fontWeight: 'bold' }
                },
                xaxis: {
                    categories: @json($chartData['studentNames'] ?? []),
                    max: 100,
                    labels: { show: false }
                },
                yaxis: {
                    labels: {
                        show: true,
                        maxWidth: 120,
                        style: { fontSize: '11px' }
                    }
                },
                legend: { show: false },
                grid: { show: false }
            };
            new ApexCharts(document.querySelector("#studentPerformanceChart"), studentPerformanceOptions).render();
            @endif

            // Performance Distribution Donut Chart
            @if(isset($chartData['distributionValues']) && array_sum($chartData['distributionValues']) > 0)
            const distributionOptions = {
                series: @json($chartData['distributionValues'] ?? []),
                chart: {
                    type: 'donut',
                    height: 250
                },
                labels: @json($chartData['distributionLabels'] ?? []),
                colors: ['#10B981', '#F59E0B', '#EF4444'],
                legend: {
                    position: 'bottom',
                    fontSize: '12px'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        return opts.w.config.series[opts.seriesIndex];
                    }
                }
            };
            new ApexCharts(document.querySelector("#performanceDistributionChart"), distributionOptions).render();
            @endif

            // Module Performance Chart
            @if(isset($chartData['moduleNames']) && count($chartData['moduleNames']) > 0)
            const moduleOptions = {
                series: [{
                    name: 'Gemiddelde %',
                    data: @json($chartData['modulePerformances'] ?? [])
                }],
                chart: {
                    type: 'column',
                    height: 320,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        distributed: true,
                        dataLabels: { position: 'top' }
                    }
                },
                colors: @json($chartData['moduleColors'] ?? []),
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val + '%';
                    },
                    offsetY: -20,
                    style: { fontSize: '12px', fontWeight: 'bold' }
                },
                xaxis: {
                    categories: @json($chartData['moduleNames'] ?? []),
                    labels: {
                        style: { fontSize: '11px' },
                        rotate: -45
                    }
                },
                yaxis: {
                    max: 100,
                    labels: {
                        formatter: function(val) {
                            return val + '%';
                        }
                    }
                },
                legend: { show: false }
            };
            new ApexCharts(document.querySelector("#modulePerformanceChart"), moduleOptions).render();
            @endif
            @endif

            // Trend Chart (if data available)
            @if(isset($trendData['values']) && count($trendData['values']) > 0)
            const trendOptions = {
                series: [{
                    name: 'Gemiddelde Score',
                    data: @json($trendData['values'])
                }],
                chart: {
                    type: 'line',
                    height: 250,
                    toolbar: { show: false }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                colors: ['#8B5CF6'],
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val + '%';
                    }
                },
                xaxis: {
                    categories: @json($trendData['dates']),
                    labels: { style: { fontSize: '11px' } }
                },
                yaxis: {
                    max: 100,
                    labels: {
                        formatter: function(val) {
                            return val + '%';
                        }
                    }
                }
            };
            new ApexCharts(document.querySelector("#trendChart"), trendOptions).render();
            @endif
        });
    </script>

    {{-- Print Styles --}}
    <style>
        @media print {
            .sidebar, nav, .no-print { display: none !important; }
            .grid { display: block !important; }
            .grid > div { margin-bottom: 1rem !important; }
            body { font-size: 12px !important; }
            .bg-white { background: white !important; }
            .shadow { box-shadow: none !important; }
            .rounded-lg { border: 1px solid #e5e7eb !important; }
        }
    </style>
@endsection
