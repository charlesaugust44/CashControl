@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/event-detail.css'])
@endpush

@section('content')
    <div class="event-detail-wrapper">
        <div class="event-delete-card">
            <div class="event-delete-header">
                <i class="bi bi-exclamation-triangle-fill event-delete-icon"></i>
                <h2>{{ __('entries.delete_confirmation.title') }}</h2>
            </div>

            <p class="event-delete-message">
                {!! __('entries.delete_confirmation.message', ['name' => e($event->name)]) !!}
            </p>

            <form method="POST" action="{{ url('/entries/' . $event->id) }}" id="deleteForm">
                @csrf
                @method('DELETE')

                <div class="form-actions">
                    <div class="form-actions__danger">
                        <button type="submit" class="btn btn-danger btn-icon" title="{{ __('ui.delete') }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
