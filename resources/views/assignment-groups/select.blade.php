@extends('layouts.layoutadmin')

@section('content')
    <div class="card mt-6">
        <div class="card-header">
            <h1 class="h6">Opdracht Groepen kiezen</h1>
        </div>
        <div class="card-body">
            <livewire:assignment-group-selector :selected-courses="$selectedCourses" :selected-modules="$selectedModules" />
        </div>
    </div>
@endsection
