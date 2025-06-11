@extends('layouts.layoutadmin')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <!-- Simplified Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 py-6 text-center">
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-gray-600">Welkom terug, {{ Auth::user()->name }}</p>
            </div>
        </div>

        <!-- Quick Action - Start Rapport -->
        <div class="max-w-7xl mx-auto px-4 py-2">
            <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl p-4 text-white mb-6 text-center">
                <h2 class="text-xl font-bold mb-2">Nieuwe Rapportage Starten</h2>
                <p class="text-green-100 mb-4">
                    Maak snel een overzicht van de voortgang van je studenten
                </p>
                <a href="{{ route('courses.index') }}"
                   class="bg-white text-green-700 px-6 py-3 rounded-lg hover:bg-gray-100 transition font-medium inline-flex items-center">
                    <i class="fas fa-play mr-2"></i>
                    Start Rapportage
                </a>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 gap-8">

                <!-- Report Types -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Beschikbare Rapporten</h3>
                    <div class="grid grid-cols-3 gap-6">

                        <div class="border rounded-lg p-6 hover:bg-gray-50 transition cursor-pointer">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-palette text-blue-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">Basis Kleur Overzicht</h4>
                            </div>
                            <p class="text-sm text-gray-600">
                                Status overzicht met kleuren
                            </p>
                        </div>

                        <div class="border rounded-lg p-6 hover:bg-gray-50 transition cursor-pointer">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-calculator text-green-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">Numerieke Cijfers</h4>
                            </div>
                            <p class="text-sm text-gray-600">
                                Toon puntscores en cijfers
                            </p>
                        </div>

                        <div class="border rounded-lg p-6 hover:bg-gray-50 transition cursor-pointer">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-percentage text-teal-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">Percentages</h4>
                            </div>
                            <p class="text-sm text-gray-600">
                                Percentage behaald vs mogelijk
                            </p>
                        </div>

                        <div class="border rounded-lg p-6 hover:bg-gray-50 transition cursor-pointer">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">Ontbrekende Opdrachten</h4>
                            </div>
                            <p class="text-sm text-gray-600">
                                Wat moet nog ingeleverd?
                            </p>
                        </div>

                        <div class="border rounded-lg p-6 hover:bg-gray-50 transition cursor-pointer">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-user-exclamation text-orange-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">Aandachtspunten</h4>
                            </div>
                            <p class="text-sm text-gray-600">
                                Studenten die hulp nodig hebben
                            </p>
                        </div>

                        <div class="border rounded-lg p-6 hover:bg-gray-50 transition cursor-pointer">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-chart-line text-purple-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">Gemiddelden</h4>
                            </div>
                            <p class="text-sm text-gray-600">
                                Per student en per opdracht
                            </p>
                        </div>

                        <div class="border rounded-lg p-6 hover:bg-gray-50 transition cursor-pointer">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-clock text-yellow-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">Deadline Overzicht</h4>
                            </div>
                            <p class="text-sm text-gray-600">
                                Te laat ingeleverde opdrachten
                            </p>
                        </div>

                        <div class="border rounded-lg p-6 hover:bg-gray-50 transition cursor-pointer">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-timeline text-indigo-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">Tijdlijn Analyse</h4>
                            </div>
                            <p class="text-sm text-gray-600">
                                Wanneer is wat ingeleverd?
                            </p>
                        </div>

                        <div class="border rounded-lg p-6 hover:bg-gray-50 transition cursor-pointer">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-file-excel text-gray-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">Excel Export</h4>
                            </div>
                            <p class="text-sm text-gray-600">
                                Download als spreadsheet
                            </p>
                        </div>

                    </div>
                </div>



            </div>



        </div>
    </div>
@endsection
