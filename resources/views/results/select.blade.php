@extends('layouts.layoutadmin')

@section('content')
    <div class="card mt-6">
        <div class="card-header">
            <h1 class="h6">Resultaat weergave kiezen</h1>
        </div>
        <div class="card-body">
            <livewire:result-selector
                :selected-courses="$selectedCourses"
                :selected-modules="$selectedModules"
                :selected-assignment-groups="$selectedAssignmentGroups"
                :selected-users="$selectedUsers"
            />
        </div>
    </div>
@endsection
