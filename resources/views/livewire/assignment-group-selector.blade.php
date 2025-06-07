<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="bg-green-400 text-green-800 rounded-lg shadow-md p-2 mb-2" style="min-width: 240px">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-400 text-red-800 rounded-lg shadow-md p-2 mb-2" style="min-width: 240px">
            {{ session('error') }}
        </div>
    @endif

    {{-- Selected Context --}}
    <div class="mb-4">
        <h2 class="font-semibold text-lg mb-2">Geselecteerde cursussen:</h2>
        <ul class="list-disc ml-6 text-sm">
            @foreach($selectedCourses as $course)
                <li>{{ $course['name'] }} (ID: {{ $course['id'] }})</li>
            @endforeach
        </ul>

        <h2 class="font-semibold text-lg mb-2 mt-4">Gekozen modules:</h2>
        <ul class="list-disc ml-6 text-sm">
            @foreach($selectedModules as $module)
                <li>{{ $module['name'] }} (Cursus: {{ $module['course_name'] }})</li>
            @endforeach
        </ul>

        {{-- Gereserveerde ruimte voor gekozen opdracht groepen --}}
        <div class="mt-4 min-h-[120px]">
            <h2 class="font-semibold text-lg mb-2">Gekozen opdracht groepen:</h2>
            @if(count($selectedAssignmentGroups) > 0)
                <ul class="list-disc ml-6 text-sm">
                    @foreach($selectedAssignmentGroups as $group)
                        <li>{{ $group['name'] }} ({{ $group['course_name'] }})
                            @if(isset($group['assignments']) && count($group['assignments']) > 0)
                                <span class="text-gray-500">- {{ count($group['assignments']) }} opdrachten</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-sm text-gray-500 italic ml-6">Nog geen opdracht groepen gekozen...</div>
            @endif
        </div>

        {{-- Next Step Button - altijd op dezelfde plek --}}
        <div class="text-right">
            @if(count($selectedAssignmentGroups) > 0)
                <button
                    wire:click="proceedToStudents"
                    class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 focus:outline-none"
                >
                    Volgende stap: Gebruikers kiezen
                </button>
            @else
                <div class="px-4 py-2 text-transparent text-sm">Volgende stap: Gebruikers kiezen</div>
            @endif
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="mb-4 flex gap-2">
        @if(count($availableAssignmentGroups) > 0)
            <button wire:click="selectAllAssignmentGroups" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                Selecteer alle groepen
            </button>
        @endif
        @if(count($selectedAssignmentGroups) > 0)
            <button wire:click="deselectAllAssignmentGroups" class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                Deselecteer alle groepen
            </button>
        @endif
    </div>

    {{-- Assignment Groups Selection Interface --}}
    <div class="flex gap-6">
        <!-- Available Assignment Groups -->
        <div class="w-1/2">
            <h3 class="font-semibold text-sm mb-2">Beschikbare opdracht groepen</h3>
            <ul class="border rounded p-2 min-h-[300px] bg-white overflow-y-auto">
                @forelse($availableAssignmentGroups as $i => $group)
                    <li class="flex justify-between items-center border-b py-2">
                        <div class="truncate flex-1">
                            <div class="font-medium text-gray-900 text-sm truncate">{{ $group['name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $group['course_name'] }}</div>
                            @if(isset($group['assignments']) && count($group['assignments']) > 0)
                                <div class="text-xs text-gray-400">{{ count($group['assignments']) }} opdrachten</div>
                                {{-- Toon eerste paar opdrachten als preview --}}
                                <div class="text-xs text-gray-400 mt-1">
                                    @foreach(array_slice($group['assignments'], 0, 2) as $assignment)
                                        <span class="inline-block bg-gray-100 rounded px-1 mr-1 mb-1">{{ $assignment['name'] }}</span>
                                    @endforeach
                                    @if(count($group['assignments']) > 2)
                                        <span class="text-gray-400">+{{ count($group['assignments']) - 2 }} meer...</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <button wire:click="selectAssignmentGroup({{ $i }})" class="ml-2 px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">→</button>
                    </li>
                @empty
                    <li class="text-xs text-gray-400">Geen opdracht groepen beschikbaar</li>
                @endforelse
            </ul>
        </div>

        <!-- Selected Assignment Groups -->
        <div class="w-1/2">
            <h3 class="font-semibold text-sm mb-2">Gekozen opdracht groepen</h3>
            <ul class="border rounded p-2 min-h-[300px] bg-white overflow-y-auto">
                @forelse($selectedAssignmentGroups as $i => $group)
                    <li class="flex justify-between items-center border-b py-2">
                        <div class="truncate flex-1">
                            <div class="font-medium text-gray-900 text-sm truncate">{{ $group['name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $group['course_name'] }}</div>
                            @if(isset($group['assignments']) && count($group['assignments']) > 0)
                                <div class="text-xs text-gray-400">{{ count($group['assignments']) }} opdrachten</div>
                            @endif
                        </div>
                        <button wire:click="deselectAssignmentGroup({{ $i }})" class="ml-2 px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">←</button>
                    </li>
                @empty
                    <li class="text-xs text-gray-400">Nog geen opdracht groepen gekozen</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
