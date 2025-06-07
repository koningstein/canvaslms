<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="bg-green-400 text-green-800 rounded-lg shadow-md p-2 mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-400 text-red-800 rounded-lg shadow-md p-2 mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Selection Summary - Two Column Layout --}}
    <div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
        {{-- Left Card: Courses, Modules, Assignment Groups --}}
        <div class="card" style="flex: 1;">
            <div class="card-header">
                <h3 class="h6">Selectie Overzicht</h3>
            </div>
            <div class="card-body">
                {{-- Courses --}}
                <div class="mb-3">
                    <h4 class="font-semibold mb-1">Cursussen ({{ count($selectedCourses) }})</h4>
                    <div class="bg-gray-50 rounded p-2 max-h-24 overflow-y-auto">
                        @forelse($selectedCourses as $course)
                            <div class="py-1">{{ $course['name'] }}</div>
                        @empty
                            <div class="text-gray-500">Geen cursussen geselecteerd</div>
                        @endforelse
                    </div>
                </div>

                {{-- Modules --}}
                <div class="mb-3">
                    <h4 class="font-semibold mb-1">Modules ({{ count($selectedModules) }})</h4>
                    <div class="bg-gray-50 rounded p-2 max-h-24 overflow-y-auto">
                        @forelse($selectedModules as $module)
                            <div class="py-1">{{ $module['name'] }}</div>
                        @empty
                            <div class="text-gray-500">Geen modules geselecteerd</div>
                        @endforelse
                    </div>
                </div>

                {{-- Assignment Groups --}}
                <div class="mb-0">
                    <h4 class="font-semibold mb-1">Opdracht groepen ({{ count($selectedAssignmentGroups) }})</h4>
                    <div class="bg-gray-50 rounded p-2 max-h-32 overflow-y-auto">
                        @forelse($selectedAssignmentGroups as $group)
                            <div class="py-1">
                                {{ $group['name'] }}
                                @if(isset($group['assignments']) && count($group['assignments']) > 0)
                                    <span class="text-gray-600">({{ count($group['assignments']) }} opdrachten)</span>
                                    <div class="text-sm text-gray-500 mt-1">
                                        @foreach(array_slice($group['assignments'], 0, 3) as $assignment)
                                            <span class="inline-block bg-gray-200 rounded px-1 mr-1 mb-1">{{ $assignment['name'] }}</span>
                                        @endforeach
                                        @if(count($group['assignments']) > 3)
                                            <span class="text-gray-500">+{{ count($group['assignments']) - 3 }} meer...</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-gray-500">Geen opdracht groepen geselecteerd</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Card: Selected Users --}}
        <div class="card" style="flex: 1;">
            <div class="card-header">
                <h3 class="h6">Geselecteerde Studenten ({{ count($selectedUsers) }})</h3>
            </div>
            <div class="card-body">
                <div class="bg-gray-50 rounded p-3 max-h-96 overflow-y-auto">
                    @forelse($selectedUsers as $user)
                        <div class="flex justify-between items-center py-1 border-b border-gray-200 last:border-b-0">
                            <div>
                                <div class="font-medium">
                                    {{ $user['name'] ?? 'Onbekend' }}
                                    <span class="text-xs text-gray-600">
                                        @if(isset($user['sis_user_id']) && $user['sis_user_id'])
                                            ({{ $user['sis_user_id'] }})
                                        @elseif(isset($user['login_id']))
                                            ({{ $user['login_id'] }})
                                        @else
                                            ({{ $user['id'] }})
                                        @endif
                                    </span>
                                    @if(isset($user['section_name']))
                                        <span class="text-xs text-blue-600 ml-2">- Sectie: {{ $user['section_name'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-gray-500">Geen studenten geselecteerd</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Report Options --}}
    <div class="mb-6">
        <h2 class="text-xl font-semibold mb-4">Kies je resultaatweergave</h2>

        <div class="card">
            <div class="card-header">
                <h3 class="h6">Rapport Opties</h3>
            </div>
            <div class="card-body">
                {{-- Rij 1: Basis Overzichten --}}
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-32 font-semibold text-gray-700">Basis Overzichten</div>
                    <div class="flex-1 grid grid-cols-3 gap-4">
                        <button
                            wire:click="generateBasicReport"
                            class="px-4 py-3 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @if(count($selectedUsers) === 0) disabled @endif
                        >
                            <div class="font-medium">Basis Kleur Overzicht</div>
                            <div class="text-sm opacity-90">Status overzicht met kleuren</div>
                        </button>

                        <button
                            wire:click="generateMissingReport"
                            class="px-4 py-3 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                            @if(count($selectedUsers) === 0) disabled @endif
                        >
                            <div class="font-medium">Ontbrekende Opdrachten</div>
                            <div class="text-sm opacity-90">Wat moet nog ingeleverd?</div>
                        </button>

                        <button
                            wire:click="generateAttentionReport"
                            class="px-4 py-3 bg-orange-600 text-white rounded hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500"
                            @if(count($selectedUsers) === 0) disabled @endif
                        >
                            <div class="font-medium">Aandachtspunten</div>
                            <div class="text-sm opacity-90">Studenten die hulp nodig hebben</div>
                        </button>
                    </div>
                </div>

                {{-- Rij 2: Cijfer & Prestatie Overzichten --}}
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-32 font-semibold text-gray-700">Cijfers & Prestaties</div>
                    <div class="flex-1 grid grid-cols-3 gap-4">
                        <button
                            wire:click="generateGradeReport"
                            class="px-4 py-3 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                            @if(count($selectedUsers) === 0) disabled @endif
                        >
                            <div class="font-medium">Numerieke Cijfers</div>
                            <div class="text-sm opacity-90">Toon puntscores en cijfers</div>
                        </button>

                        <button
                            wire:click="generatePercentageReport"
                            class="px-4 py-3 bg-teal-600 text-white rounded hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500"
                            @if(count($selectedUsers) === 0) disabled @endif
                        >
                            <div class="font-medium">Percentages</div>
                            <div class="text-sm opacity-90">Percentage behaald vs mogelijk</div>
                        </button>

                        <button
                            wire:click="generateAverageReport"
                            class="px-4 py-3 bg-purple-600 text-white rounded hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
                            @if(count($selectedUsers) === 0) disabled @endif
                        >
                            <div class="font-medium">Gemiddelden</div>
                            <div class="text-sm opacity-90">Per student en per opdracht</div>
                        </button>
                    </div>
                </div>

                {{-- Rij 3: Tijd & Analyse --}}
                <div class="flex items-center gap-4 mb-0">
                    <div class="w-32 font-semibold text-gray-700">Tijd & Analyse</div>
                    <div class="flex-1 grid grid-cols-3 gap-4">
                        <button
                            wire:click="generateDeadlineReport"
                            class="px-4 py-3 bg-yellow-600 text-white rounded hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500"
                            @if(count($selectedUsers) === 0) disabled @endif
                        >
                            <div class="font-medium">Deadline Overzicht</div>
                            <div class="text-sm opacity-90">Te laat ingeleverde opdrachten</div>
                        </button>

                        <button
                            wire:click="generateTimelineReport"
                            class="px-4 py-3 bg-indigo-600 text-white rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            @if(count($selectedUsers) === 0) disabled @endif
                        >
                            <div class="font-medium">Tijdlijn Analyse</div>
                            <div class="text-sm opacity-90">Wanneer is wat ingeleverd?</div>
                        </button>

                        <button
                            wire:click="generateCompetencyReport"
                            class="px-4 py-3 bg-pink-600 text-white rounded hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500"
                            @if(count($selectedUsers) === 0) disabled @endif
                        >
                            <div class="font-medium">Competentie Overzicht</div>
                            <div class="text-sm opacity-90">MBO competentie voortgang</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Export Option --}}
    <div class="text-center">
        <div class="card inline-block">
            <div class="card-body">
                <button
                    wire:click="generateExcelExport"
                    class="px-6 py-3 bg-gray-700 text-white rounded hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-600"
                    @if(count($selectedUsers) === 0) disabled @endif
                >
                    <div class="font-medium">Excel Export</div>
                    <div class="text-sm opacity-90">Download als spreadsheet</div>
                </button>
            </div>
        </div>
    </div>

    @if(count($selectedUsers) === 0)
        <div class="mt-4 text-center text-gray-600">
            <p>Selecteer eerst studenten om rapporten te kunnen genereren</p>
        </div>
    @endif
</div>
