@extends('layouts.layoutadmin')

@section('content')
    <div class="card mt-6">
        <div class="card-header">
            <h1 class="h6">Gebruikers kiezen</h1>
        </div>
        <div class="card-body">
            <livewire:student-selector
                :selected-courses="$selectedCourses"
                :selected-modules="session('selected_modules', [])"
                :selected-assignment-groups="session('selected_assignment_groups', [])"
            />
        </div>
    </div>
@endsection
