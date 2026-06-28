@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/templates.css'])
@endpush

@section('content')
    <div class="template-delete-wrapper">
        <div class="template-delete-card">
            <div class="template-delete-header">
                <i class="bi bi-exclamation-triangle-fill template-delete-icon"></i>
                <h2>Delete Template</h2>
            </div>

            <p class="template-delete-message">
                Are you sure you want to delete <strong>{{ $header->name }}</strong>? This action cannot be undone.
            </p>

            @if($futureEvents->isNotEmpty())
                <div class="conflict-section">
                    <h3 class="conflict-section__title">Affected Future Events</h3>
                    <p class="conflict-section__description">These events will be affected by this deletion. Choose whether to keep them as-is or delete them.</p>

                    <form method="POST" action="{{ url('/templates/' . $header->id) }}" id="deleteForm">
                        @csrf
                        @method('DELETE')

                        <div class="conflict-events-list">
                            @foreach($futureEvents as $event)
                                <div class="conflict-event">
                                    <div class="conflict-event__info">
                                        <span class="conflict-event__date">{{ $event->date->format('M Y') }}</span>
                                        @foreach($event->entries as $entry)
                                            <span class="conflict-event__entry">
                                                <i class="bi bi-wallet2"></i>
                                                {{ $entry->asset->name ?? 'Unknown' }}:
                                                <span class="{{ $entry->amount >= 0 ? 'positive' : 'negative' }}">
                                                    {{ number_format(abs($entry->amount), 2) }}
                                                </span>
                                            </span>
                                        @endforeach
                                    </div>
                                    <div class="conflict-event__actions">
                                        <label class="conflict-toggle">
                                            <input type="checkbox" name="delete_events[]" value="{{ $event->id }}" checked>
                                            <span class="conflict-toggle__label">Delete</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="template-delete-actions">
                            <a href="{{ url('/templates/' . $header->id) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Delete Template
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ url('/templates/' . $header->id) }}">
                    @csrf
                    @method('DELETE')

                    <div class="template-delete-actions">
                        <a href="{{ url('/templates/' . $header->id) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete Template
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
