@extends('layouts.layoutadmin')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Voortgang Studenten per Cursus en Module</h1>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse border border-gray-300">
            <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2 border border-gray-300 text-left text-sm font-medium text-gray-700">
                    Student
                </th>
                @foreach($studentsProgress->first()['assignments']->groupBy('module_name') as $moduleName => $assignments)
                    <th class="px-4 py-2 border border-gray-300 text-center text-sm font-medium text-gray-700" colspan="{{ $assignments->count() }}">
                        {{ $moduleName }}
                    </th>
                    <th class="w-1 bg-gray-200 border-0"></th>
                @endforeach
            </tr>
            <tr class="bg-gray-50">
                <th class="px-4 py-2 border border-gray-300 text-left text-sm font-medium text-gray-700">
                    &nbsp;
                </th>
                @foreach($studentsProgress->first()['assignments']->groupBy('module_name') as $moduleName => $assignments)
                    @foreach($assignments as $assignment)
                        <th class="px-2 py-2 border border-gray-300 text-center text-sm font-medium text-gray-700" style="writing-mode: vertical-lr;">
                            {{ $assignment['assignment_name'] }}
                        </th>
                    @endforeach
                    <th class="w-1 bg-gray-200 border-0"></th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($studentsProgress as $student)
                <tr class="bg-white hover:bg-gray-50">
                    <td class="px-4 py-2 border border-gray-300 text-sm text-gray-800">
                        {{ $student['student_name'] }} (ID: {{ $student['student_id'] }})
                    </td>
                    @foreach($studentsProgress->first()['assignments']->groupBy('module_name') as $moduleName => $assignments)
                        @foreach($assignments as $assignment)
                            <td class="px-4 py-2 border border-gray-300 text-sm text-center {{ $student['assignments']->where('assignment_name', $assignment['assignment_name'])->first()['color'] ?? 'bg-gray-100' }}">
                                {{ ucfirst($student['assignments']->where('assignment_name', $assignment['assignment_name'])->first()['status'] ?? '') }}
                            </td>
                        @endforeach
                        <td class="w-1 bg-gray-200 border-0"></td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
