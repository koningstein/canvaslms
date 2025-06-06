@extends('layouts.layoutadmin')

@section('content')
    <div class="card mt-6">
        <div class="card-header">
            <h1 class="h6">Canvas Course Selector</h1>
        </div>
        <div class="card-body">
            <livewire:course-selector />
        </div>
    </div>
@endsection
