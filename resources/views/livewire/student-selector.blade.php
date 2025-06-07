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
    @if (session()->has('warning'))
        <div class="bg-yellow-400 text-yellow-800 rounded-lg shadow-md p-2 mb-2" style="min-width: 240px">
            {{ session('warning') }}
        </div>
    @endif

    <!-- Selected Courses, Modules and Assignment Groups -->
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

        <h2 class="font-semibold text-lg mb-2 mt-4">Gekozen opdracht groepen:</h2>
        @if(count($selectedAssignmentGroups) > 0)
            <ul class="list-disc ml-6 text-sm">
                @foreach($selectedAssignmentGroups as $group)
                    <li>
                        {{ $group['name'] }} ({{ $group['course_name'] }})
                        @if(isset($group['assignments']) && count($group['assignments']) > 0)
                            <span class="text-gray-500">- {{ count($group['assignments']) }} opdrachten</span>
                            <div class="text-xs text-gray-600 mt-1 ml-4">
                                @foreach(array_slice($group['assignments'], 0, 3) as $assignment)
                                    <span class="inline-block bg-gray-200 text-gray-800 rounded px-1 mr-1 mb-1">{{ $assignment['name'] }}</span>
                                @endforeach
                                @if(count($group['assignments']) > 3)
                                    <span class="text-gray-600">+{{ count($group['assignments']) - 3 }} meer...</span>
                                @endif
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-sm text-gray-500 italic ml-6">Geen opdracht groepen geselecteerd</div>
        @endif

        <!-- Next Step Button - Fixed height section with reserved space -->
        <div class="mt-4 text-right h-10 flex items-start justify-end">
            @if(count($selectedUsers) > 0)
                <button wire:click="showResult" class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">Volgende stap: Toon resultaat</button>
            @endif
        </div>
    </div>

    <!-- Action Buttons - Fixed height section -->
    <div class="mb-4 flex gap-2 items-start flex-wrap">
        @if(count($availableUsers) > 0)
            <button wire:click="selectAllUsers" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">Selecteer alle gebruikers</button>
        @endif
        @if(count($selectedUsers) > 0)
            <button wire:click="deselectAllUsers" class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">Deselecteer alle gebruikers</button>
        @endif
        @foreach($availableSections as $section)
            <button
                wire:click="selectUsersFromSection({{ $section['id'] }}, '{{ $section['name'] }}')"
                class="px-4 py-2 bg-purple-600 text-white text-sm rounded hover:bg-purple-700 focus:outline-none"
                title="Selecteer alle studenten uit sectie {{ $section['name'] }}"
            >
                Sectie: {{ $section['name'] }}
            </button>
        @endforeach
    </div>

    <!-- User Selection Interface -->
    <div class="flex gap-6">
        <!-- Available Users -->
        <div class="w-1/2">
            <h3 class="font-semibold text-sm mb-2">Beschikbare gebruikers</h3>
            <ul class="border rounded p-2 min-h-[200px] bg-white">
                @forelse($availableUsers as $i => $user)
                    <li class="flex justify-between items-center border-b py-1">
                        <span class="truncate">{{ $user['name'] ?? 'Onbekende gebruiker' }} <span class="text-xs text-gray-600">({{ $user['email'] ?? $user['login_id'] ?? 'ID: ' . $user['id'] }})</span></span>
                        <button wire:click="selectUser({{ $i }})" class="ml-2 px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">→</button>
                    </li>
                @empty
                    <li class="text-xs text-gray-400">Geen gebruikers beschikbaar</li>
                @endforelse
            </ul>
        </div>
        <!-- Selected Users -->
        <div class="w-1/2">
            <h3 class="font-semibold text-sm mb-2">Geselecteerde gebruikers</h3>
            <ul class="border rounded p-2 min-h-[200px] bg-white">
                @forelse($selectedUsers as $i => $user)
                    <li class="flex justify-between items-center border-b py-1">
                        <div class="truncate flex-1">
                            <div class="truncate">
                                <span class="font-medium">{{ $user['name'] ?? 'Onbekende gebruiker' }}</span>
                                <span class="text-xs text-gray-600 ml-1">({{ $user['email'] ?? $user['login_id'] ?? 'ID: ' . $user['id'] }})</span>
                                @if(isset($user['section_name']))
                                    <span class="text-xs text-blue-600 ml-2">- Sectie: {{ $user['section_name'] }}</span>
                                @endif
                            </div>
                        </div>
                        <button wire:click="deselectUser({{ $i }})" class="ml-2 px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">←</button>
                    </li>
                @empty
                    <li class="text-xs text-gray-400">Nog geen gebruikers gekozen</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
