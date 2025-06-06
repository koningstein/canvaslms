<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="bg-green-400 text-green-800 rounded-lg shadow-md p-6 pr-10 mb-8" style="min-width: 240px">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-400 text-red-800 rounded-lg shadow-md p-6 pr-10 mb-8" style="min-width: 240px">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="bg-yellow-400 text-yellow-800 rounded-lg shadow-md p-6 pr-10 mb-8" style="min-width: 240px">
            {{ session('warning') }}
        </div>
    @endif

    {{-- Search Section --}}
    <div class="card">
        <div class="card-header">
            <h1 class="h6">Canvas Cursussen Zoeken</h1>
        </div>
        <div class="py-4 px-6">
            <div class="mb-4">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchTerm"
                    placeholder="Zoek cursussen... (laat leeg voor alle cursussen)"
                    class="bg-gray-200 block rounded w-full p-2 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-input"
                />
            </div>

            {{-- Search Results --}}
            @if(count($searchResults) > 0)
                <div class="mt-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">
                        @if($searchTerm)
                            Zoekresultaten voor "{{ $searchTerm }}":
                        @else
                            Je beschikbare cursussen:
                        @endif
                    </h3>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($searchResults as $course)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded border">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $course['name'] }}</h4>
                                    @if(isset($course['course_code']) && $course['course_code'])
                                        <p class="text-sm text-gray-600">Code: {{ $course['course_code'] }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">ID: {{ $course['id'] }}</p>
                                </div>
                                <button
                                    wire:click="selectCourse({{ json_encode($course) }})"
                                    class="px-3 py-1 text-xs font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"
                                >
                                    Selecteer
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($searchTerm !== '' && count($searchResults) === 0)
                <div class="mt-4 p-4 bg-yellow-50 rounded border border-yellow-200">
                    <p class="text-yellow-800">Geen cursussen gevonden</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Selected Courses Section --}}
    <div class="card mt-6">
        <div class="card-header flex flex-row justify-between">
            <h1 class="h6">Geselecteerde Cursussen</h1>
            @if(count($selectedCourses) > 0)
                <span class="text-sm text-gray-600">({{ count($selectedCourses) }})</span>
            @endif
        </div>
        <div class="py-4 px-6">
            @if(count($selectedCourses) > 0)
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($selectedCourses as $course)
                        <div class="border border-gray-200 rounded p-4 bg-white hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 text-sm">{{ $course['name'] }}</h4>
                                    @if(isset($course['course_code']) && $course['course_code'])
                                        <p class="text-xs text-gray-600 mt-1">{{ $course['course_code'] }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500 mt-1">Canvas ID: {{ $course['id'] }}</p>
                                </div>
                                <button
                                    wire:click="removeCourse({{ $course['id'] }})"
                                    class="ml-2 p-1 text-red-600 hover:text-red-800 focus:outline-none"
                                    title="Verwijder cursus"
                                >
                                    <i class="fad fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fad fa-books text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-sm font-medium text-gray-900">Geen cursussen geselecteerd</h3>
                    <p class="text-sm text-gray-500 mt-1">Begin met zoeken naar cursussen om ze te selecteren.</p>
                </div>
            @endif
        </div>
    </div>
</div>
