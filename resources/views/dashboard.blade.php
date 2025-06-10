@extends('layouts.layoutadmin')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <!-- Hero Section + Quick Stats in 2 columns -->
        <div class="bg-gradient-to-br from-green-900 via-green-800 to-green-700 text-white">
            <div class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <!-- Left: Hero Content -->
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold mb-4">
                            Canvas Tool Dashboard
                        </h1>
                        <p class="text-lg text-green-100 mb-6">
                            Welkom {{ Auth::user()->name }}! Alle tools en inzichten die je nodig hebt om je studenten effectief te begeleiden.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="{{ route('courses.index') }}"
                               class="bg-white text-green-800 font-semibold py-3 px-6 rounded-lg shadow-lg hover:bg-gray-100 transition duration-300">
                                Start Nieuwe Rapportage
                            </a>
                            <button class="bg-green-600 text-white font-semibold py-3 px-6 rounded-lg shadow-lg hover:bg-green-500 transition duration-300">
                                Bekijk Handleiding
                            </button>
                        </div>
                    </div>

                    <!-- Right: Quick Stats -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white bg-opacity-10 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-white mb-1">{{ $stats['reports_this_month'] ?? 24 }}</div>
                            <div class="text-green-200 text-sm">Rapporten Deze Maand</div>
                        </div>
                        <div class="bg-white bg-opacity-10 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-white mb-1">{{ $stats['monitored_students'] ?? 156 }}</div>
                            <div class="text-green-200 text-sm">Studenten</div>
                        </div>
                        <div class="bg-white bg-opacity-10 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-white mb-1">{{ $stats['active_courses'] ?? 12 }}</div>
                            <div class="text-green-200 text-sm">Actieve Cursussen</div>
                        </div>
                        <div class="bg-white bg-opacity-10 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-white mb-1">{{ $stats['saved_reports'] ?? 8 }}</div>
                            <div class="text-green-200 text-sm">Opgeslagen</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Row -->
        <div class="max-w-7xl mx-auto px-4 -mt-6 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-600">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full mr-4">
                            <i class="fas fa-bolt text-blue-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900">Snel Starten</h3>
                            <p class="text-gray-600 text-sm">Direct een rapport maken</p>
                        </div>
                        <a href="{{ route('courses.index') }}"
                           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                            Start
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-600">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full mr-4">
                            <i class="fas fa-bookmark text-green-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900">Opgeslagen</h3>
                            <p class="text-gray-600 text-sm">Hergebruik instellingen</p>
                        </div>
                        <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm">
                            Bekijk
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-600">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-full mr-4">
                            <i class="fas fa-cog text-purple-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900">Instellingen</h3>
                            <p class="text-gray-600 text-sm">Configureer systeem</p>
                        </div>
                        <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition text-sm">
                            Setup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: 2 Column Layout -->
        <div class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left Column: Features (2/3 width) -->
                <div class="lg:col-span-2">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Krachtige Features voor Docenten</h2>
                        <p class="text-gray-600">Alle rapportage opties die je nodig hebt voor effectieve student begeleiding</p>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Feature 1 -->
                        <div class="feature-card bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                            <div class="bg-blue-600 rounded-lg p-3 w-12 h-12 flex items-center justify-center mb-4">
                                <i class="fas fa-palette text-white text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-3">Kleur Overzichten</h3>
                            <p class="text-gray-700 text-sm leading-relaxed">
                                Krijg direct inzicht met intuïtieve kleurcodering. Groen voor goed, geel voor voldoende, rood voor aandacht.
                            </p>
                        </div>

                        <!-- Feature 2 -->
                        <div class="feature-card bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <div class="bg-green-600 rounded-lg p-3 w-12 h-12 flex items-center justify-center mb-4">
                                <i class="fas fa-calculator text-white text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-3">Cijfer Analyses</h3>
                            <p class="text-gray-700 text-sm leading-relaxed">
                                Numerieke cijfers, percentages en gemiddelden per student. Inclusief trend analyses en statistieken.
                            </p>
                        </div>

                        <!-- Feature 3 -->
                        <div class="feature-card bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 border border-red-200">
                            <div class="bg-red-600 rounded-lg p-3 w-12 h-12 flex items-center justify-center mb-4">
                                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-3">Aandachtspunten</h3>
                            <p class="text-gray-700 text-sm leading-relaxed">
                                Identificeer automatisch studenten die extra begeleiding nodig hebben op basis van resultaten.
                            </p>
                        </div>

                        <!-- Feature 4 -->
                        <div class="feature-card bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                            <div class="bg-purple-600 rounded-lg p-3 w-12 h-12 flex items-center justify-center mb-4">
                                <i class="fas fa-filter text-white text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-3">Flexibele Selectie</h3>
                            <p class="text-gray-700 text-sm leading-relaxed">
                                Kies precies welke cursussen, modules en studenten je wilt analyseren. Volledig configureerbaar.
                            </p>
                        </div>

                        <!-- Feature 5 -->
                        <div class="feature-card bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                            <div class="bg-yellow-600 rounded-lg p-3 w-12 h-12 flex items-center justify-center mb-4">
                                <i class="fas fa-file-export text-white text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-3">Export Opties</h3>
                            <p class="text-gray-700 text-sm leading-relaxed">
                                Exporteer naar Excel of print direct. Ideaal voor ouderavonden en team evaluaties.
                            </p>
                        </div>

                        <!-- Feature 6 -->
                        <div class="feature-card bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 border border-indigo-200">
                            <div class="bg-indigo-600 rounded-lg p-3 w-12 h-12 flex items-center justify-center mb-4">
                                <i class="fas fa-clock text-white text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-3">Real-time Data</h3>
                            <p class="text-gray-700 text-sm leading-relaxed">
                                Directe Canvas API koppeling voor actuele gegevens. Geen verouderde data meer.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar: Recent Activity & Tips (1/3 width) -->
                <div class="space-y-6">
                    <!-- Recent Activity -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-clock text-gray-500 mr-2"></i>
                            Recente Activiteit
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                <div class="flex-1">
                                    <div class="text-gray-900 font-medium">Rapport gegenereerd</div>
                                    <div class="text-gray-500">Software Dev 2A - 2 uur geleden</div>
                                </div>
                            </div>
                            <div class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                <div class="flex-1">
                                    <div class="text-gray-900 font-medium">Nieuwe instellingen opgeslagen</div>
                                    <div class="text-gray-500">Module selectie - vandaag</div>
                                </div>
                            </div>
                            <div class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-purple-500 rounded-full mr-3"></div>
                                <div class="flex-1">
                                    <div class="text-gray-900 font-medium">Excel export voltooid</div>
                                    <div class="text-gray-500">Voortgang analyse - gisteren</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Tips -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                            Snelle Tips
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-start">
                                <div class="text-green-600 mr-2">💡</div>
                                <div>
                                    <div class="font-medium text-gray-900">Bewaar je instellingen</div>
                                    <div class="text-gray-600">Gebruik dezelfde configuratie voor wekelijkse rapporten</div>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="text-green-600 mr-2">⚡</div>
                                <div>
                                    <div class="font-medium text-gray-900">Kleur overzichten</div>
                                    <div class="text-gray-600">Start met kleur rapporten voor snel overzicht</div>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="text-green-600 mr-2">📊</div>
                                <div>
                                    <div class="font-medium text-gray-900">Export naar Excel</div>
                                    <div class="text-gray-600">Voor uitgebreide analyses en archivering</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Getting Started -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-dashed border-gray-300">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 text-center">Aan de slag</h3>
                        <div class="text-center">
                            <div class="text-6xl mb-4">🚀</div>
                            <p class="text-gray-600 mb-4 text-sm">
                                Klaar om je eerste rapport te maken?
                            </p>
                            <a href="{{ route('courses.index') }}"
                               class="bg-green-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700 transition duration-300 block">
                                Start Eerste Rapport
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
