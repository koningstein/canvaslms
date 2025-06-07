<div>
    <div class="mb-4">
        <h2 class="font-semibold text-lg mb-2">Geselecteerde cursussen:</h2>
        <ul class="list-disc ml-6 text-sm">
            @foreach($selectedCourses as $course)
                <li>{{ $course['name'] }} (ID: {{ $course['id'] }})</li>
            @endforeach
        </ul>
    </div>
    <div class="mt-4 text-right">
        @if(count($selectedModules) > 0)
            <button
                wire:click="proceedToAssignmentGroups"
                class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 focus:outline-none"
            >
                Volgende stap: Opdracht groepen kiezen
            </button>
        @endif
    </div>
    <div class="flex gap-6">
        <!-- Available Modules -->
        <div class="w-1/2">
            <h3 class="font-semibold text-sm mb-2">Beschikbare modules</h3>
            <ul class="border rounded p-2 min-h-[200px] bg-white">
                @forelse($availableModules as $i => $module)
                    <li class="flex justify-between items-center border-b py-1">
                        <span class="truncate">{{ $module['name'] ?? 'Onbekende module' }} <span class="text-xs text-gray-400">({{ $module['course_name'] }})</span></span>
                        <button wire:click="selectModule({{ $i }})" class="ml-2 px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">→</button>
                    </li>
                @empty
                    <li class="text-xs text-gray-400">Geen modules beschikbaar</li>
                @endforelse
            </ul>
        </div>
        <!-- Selected Modules -->
        <div class="w-1/2">
            <h3 class="font-semibold text-sm mb-2">Gekozen modules</h3>
            <ul class="border rounded p-2 min-h-[200px] bg-white">
                @forelse($selectedModules as $i => $module)
                    <li class="flex justify-between items-center border-b py-1">
                        <span class="truncate">{{ $module['name'] ?? 'Onbekende module' }} <span class="text-xs text-gray-400">({{ $module['course_name'] }})</span></span>
                        <button wire:click="deselectModule({{ $i }})" class="ml-2 px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">←</button>
                    </li>
                @empty
                    <li class="text-xs text-gray-400">Nog geen modules gekozen</li>
                @endforelse
            </ul>
        </div>
    </div>

</div>
