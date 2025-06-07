<div>
    <!-- Selected Courses and Modules -->
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
        <div class="mt-4 text-right">
            @if(count($selectedUsers) > 0)
                <button wire:click="showResult" class="mt-2 px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">Volgende stap: Toon resultaat</button>
            @endif
        </div>
    </div>

    <!-- User Selection Interface -->
    <!-- Section Buttons (full width above both lists) -->
    <div class="mb-4 flex flex-wrap gap-2 w-full">
        @foreach($sections as $section)
            <button
                wire:click="selectAllUsersInSectionsButton('{{ $section['id'] }}')"
                class="flex-1 min-w-[150px] px-2 py-2 text-xs rounded text-center
                {{ $lastSelectedSectionId == $section['id'] ? 'bg-purple-800 text-white font-bold border-2 border-purple-900' : 'bg-purple-600 text-white hover:bg-purple-700' }}"
            >
                Select all from {{ $section['name'] }}
            </button>
        @endforeach
    </div>

    <div class="flex gap-6">
        <!-- Available Users -->
        <div class="w-1/2">
            <h3 class="font-semibold text-sm mb-2">Beschikbare gebruikers</h3>
            @if(count($availableUsers) > 0)
                <button wire:click="selectAllUsers" class="mb-2 px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">Selecteer alle gebruikers</button>
            @endif
            <ul class="border rounded p-2 min-h-[200px] bg-white">
                @forelse($availableUsers as $i => $user)
                    <li class="flex justify-between items-center border-b py-1">
                        <span class="truncate">{{ $user['name'] ?? 'Onbekende gebruiker' }} <span class="text-xs text-gray-400">({{ $user['email'] }})</span></span>
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
            @if(count($selectedUsers) > 0)
                <button wire:click="deselectAllUsers" class="mb-2 px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">Deselecteer alle gebruikers</button>
            @endif
            <ul class="border rounded p-2 min-h-[200px] bg-white">
                @forelse($selectedUsers as $i => $user)
                    <li class="flex justify-between items-center border-b py-1">
                        <span class="truncate">{{ $user['name'] ?? 'Onbekende gebruiker' }} <span class="text-xs text-gray-400">({{ $user['email'] }})</span></span>
                        <button wire:click="deselectUser({{ $i }})" class="ml-2 px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">←</button>
                    </li>
                @empty
                    <li class="text-xs text-gray-400">Nog geen gebruikers gekozen</li>
                @endforelse
            </ul>

        </div>
    </div>
</div>
