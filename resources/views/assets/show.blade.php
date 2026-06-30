@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/asset-detail.css'])
@endpush

@section('content')
    <div class="asset-detail-container">
        @component('components.asset-header', ['total' => $asset->balance ?? 0])
        @endcomponent
        <div class="entries-section">
            @if($events->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="empty-title">{{ __('ui.no_results') }}</h3>
                    <p class="empty-description">
                        {{ __('entries.no_entries') }}
                    </p>
                </div>
            @else
                <div class="list-wrapper">
                    @foreach($events as $event)
                        <div class="list-item">
                            @include('components.entry-item', ['event' => $event])
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
