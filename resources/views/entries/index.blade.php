@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/entries.css'])
@endpush

@section('content')
    <div class="entries-container">
        @include('components.month-picker', ['currentMonth' => $currentMonth])
        <div class="list-wrapper">
            @foreach($events as $event)
                <div class="list-item">
                    @include('components.entry-item', ['event' => $event])
                </div>
            @endforeach
        </div>
    </div>
@endsection
