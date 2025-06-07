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

    {{-- Selected Courses Section (now at the top) --}}
    <div class="card mb-4">
        <div class="card-header flex flex-row justify-between py-2 px-4">
            <h1 class="h6 text-sm">Geselecteerde Cursussen</h1>
            @if(count($selectedCourses) > 0)
                <span class="text-xs text-gray-600">({{ count($selectedCourses) }})</span>
            @endif
        </div>
        <div class="py-2 px-4">
            @if(count($selectedCourses) > 0)
                <div class="space-y-1">
                    @foreach($selectedCourses as $index => $course)
                        <div class="flex items-center justify-between border-b border-gray-100 py-1 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-200' }} px-2 rounded hover:bg-blue-50 transition-colors duration-150">
                            <div class="truncate">
                                <span class="font-medium text-gray-900 text-sm truncate">{{ $course['name'] }}</span>
                                @if(isset($course['course_code']) && $course['course_code'])
                                    <span class="text-xs text-gray-500 ml-2">{{ $course['course_code'] }}</span>
                                @endif
                                <span class="text-xs text-gray-400 ml-2">ID: {{ $course['id'] }}</span>
                            </div>
                            <button
                                wire:click="removeCourse({{ $course['id'] }})"
                                class="ml-2 p-1 text-red-600 hover:text-red-800 focus:outline-none"
                                title="Verwijder cursus"
                            >
                                <i class="fad fa-trash text-xs"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 text-right">
                    <button
                        wire:click="proceedToModules"
                        class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 focus:outline-none"
                    >
                        Volgende stap: Modules kiezen
                    </button>
                </div>
            @else
                <div class="text-center py-2">
                    <span class="text-xs text-gray-500">Geen cursussen geselecteerd</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Search Section --}}
    <div class="card">
        <div class="card-header py-2 px-4">
            <h1 class="h6 text-sm">Canvas Cursussen Zoeken</h1>
        </div>
        <div class="py-2 px-4">
            <div class="mb-2">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchTerm"
                    placeholder="Zoek cursussen... (laat leeg voor alle cursussen)"
                    class="bg-gray-200 block rounded w-full p-2 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-input"
                />
            </div>

            {{-- Search Results --}}
            @if(count($searchResults) > 0)
                <div>
                    <h3 class="text-xs font-semibold text-gray-700 mb-1">
                        @if($searchTerm)
                            Zoekresultaten voor "{{ $searchTerm }}":
                        @else
                            Je beschikbare cursussen:
                        @endif
                    </h3>
                    <div class="space-y-1 max-h-48 overflow-y-auto">
                        @foreach($searchResults as $index => $course)
                            <div class="flex items-center justify-between border-b border-gray-100 py-2 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-200' }} px-2 rounded hover:bg-blue-100 transition-colors duration-150">
                                <div class="truncate">
                                    <span class="font-medium text-gray-900 text-sm truncate">{{ $course['name'] }}</span>
                                    @if(isset($course['course_code']) && $course['course_code'])
                                        <span class="text-xs text-gray-500 ml-2">{{ $course['course_code'] }}</span>
                                    @endif
                                    <span class="text-xs text-gray-400 ml-2">ID: {{ $course['id'] }}</span>
                                </div>
                                <button
                                    wire:click="selectCourse({{ json_encode($course) }})"
                                    class="ml-2 px-2 py-1 text-xs font-medium text-white bg-purple-600 rounded hover:bg-purple-700 focus:outline-none"
                                >
                                    Selecteer
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($searchTerm !== '' && count($searchResults) === 0)
                <div class="mt-2 p-2 bg-yellow-50 rounded border border-yellow-200">
                    <p class="text-xs text-yellow-800">Geen cursussen gevonden</p>
                </div>
            @endif
        </div>
    </div>
</div>
