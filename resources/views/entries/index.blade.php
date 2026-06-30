@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/entries.css'])
@endpush

@section('content')
    <div class="entries-container">
        <div class="entries-top-bar">
            <div class="entries-month-row">
                <div class="month-picker-wrapper">
                    @include('components.month-picker', ['currentMonth' => $currentMonth])
                </div>

                <div class="entries-actions">
                    <a href="{{ url('/entries/create?month=' . $currentMonth) }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i>
                        {{ __('entries.create') }}
                    </a>
                </div>
            </div>

            <div class="entries-summary">
                <div class="summary-card summary-card--income">
                    <div class="summary-card__icon">
                        <i class="bi bi-arrow-down-left"></i>
                    </div>
                    <div class="summary-card__content">
                        <span class="summary-card__label">{{ __('templates.types.income') }}</span>
                        <span class="summary-card__value">{{ $fmt->currency($totalIncome) }}</span>
                    </div>
                </div>
                <div class="summary-card summary-card--expense">
                    <div class="summary-card__icon">
                        <i class="bi bi-arrow-up-right"></i>
                    </div>
                    <div class="summary-card__content">
                        <span class="summary-card__label">{{ __('templates.types.expense') }}</span>
                        <span class="summary-card__value">{{ $fmt->currency($totalExpense) }}</span>
                    </div>
                </div>
                <div class="summary-card summary-card--balance {{ $balance >= 0 ? 'summary-card--positive' : 'summary-card--negative' }}">
                    <div class="summary-card__icon">
                        <i class="bi bi-{{ $balance >= 0 ? 'cash' : 'exclamation-circle' }}"></i>
                    </div>
                    <div class="summary-card__content">
                        <span class="summary-card__label">{{ __('entries.balance') }}</span>
                        <span class="summary-card__value">{{ $fmt->currency(abs($balance)) }}</span>
                    </div>
                </div>
            </div>

            <div class="entries-filter">
                <div class="filter-tabs">
                    @php
                        $baseUrl = request()->url();
                        $monthParam = request('month', now()->format('Y-m'));
                        $filters = [
                            'all' => ['label' => __('ui.all'), 'icon' => 'bi-grid-3x3-gap'],
                            'income' => ['label' => __('templates.types.income'), 'icon' => 'bi-arrow-down-left'],
                            'expense' => ['label' => __('templates.types.expense'), 'icon' => 'bi-arrow-up-right'],
                            'transfer' => ['label' => __('templates.types.transfer'), 'icon' => 'bi-arrow-left-right'],
                        ];
                    @endphp
                    @foreach($filters as $key => $filterConfig)
                        <a href="{{ $baseUrl }}?month={{ $monthParam }}&filter={{ $key }}"
                           class="filter-tab {{ $currentFilter === $key ? 'filter-tab--active' : '' }}">
                            <i class="bi {{ $filterConfig['icon'] }}"></i>
                            <span>{{ $filterConfig['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="list-wrapper">
            @forelse($events as $event)
                <div class="list-item">
                    @include('components.entry-item', ['event' => $event])
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1"></i>
                    <p>{{ __('entries.no_entries') }}</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
