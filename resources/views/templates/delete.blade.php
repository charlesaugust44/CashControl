@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/templates.css'])
@endpush

@section('content')
    <div class="template-delete-wrapper">
        <div class="template-delete-card">
            <div class="template-delete-header">
                <i class="bi bi-exclamation-triangle-fill template-delete-icon"></i>
                <h2>{{ __('templates.delete_confirmation.title') }}</h2>
            </div>

            <p class="template-delete-message">
                {!! __('templates.delete_confirmation.message', ['name' => e($header->name)]) !!}
            </p>

            @if($futureEvents->isNotEmpty())
                <div class="conflict-section">
                    <h3 class="conflict-section__title">{{ __('templates.affected_events.title') }}</h3>
                    <p class="conflict-section__description">{{ __('templates.affected_events.delete_description') }}</p>

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
                                                {{ $entry->asset->name ?? __('ui.none') }}:
                                                <span class="{{ $entry->amount >= 0 ? 'positive' : 'negative' }}">
                                                    {{ $fmt->currency(abs($entry->amount)) }}
                                                </span>
                                            </span>
                                        @endforeach
                                    </div>
                                    <div class="conflict-event__actions">
                                        <label class="conflict-toggle">
                                            <input type="checkbox" name="delete_events[]" value="{{ $event->id }}" checked>
                                            <span class="conflict-toggle__label">{{ __('templates.affected_events.delete') }}</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="template-delete-actions">
                            <a href="{{ url('/templates/' . $header->id) }}" class="btn btn-outline-secondary">{{ __('ui.cancel') }}</a>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> {{ __('templates.delete') }}
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ url('/templates/' . $header->id) }}">
                    @csrf
                    @method('DELETE')

                    <div class="template-delete-actions">
                        <a href="{{ url('/templates/' . $header->id) }}" class="btn btn-outline-secondary">{{ __('ui.cancel') }}</a>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> {{ __('templates.delete') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
