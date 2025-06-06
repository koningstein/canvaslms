@extends('layouts.layoutadmin')

@section('content')
    <div class="card mt-6">
        <div class="card-header">
            <h1 class="h6">Modules kiezen</h1>
        </div>
        <div class="card-body">
            <livewire:module-selector :selected-courses="$selectedCourses" />
        </div>
    </div>
@endsection
