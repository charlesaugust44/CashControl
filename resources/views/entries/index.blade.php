@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/entries.css'])
@endpush

@section('content')
    <div class="entries-container">
        <div class="entries-top-bar">
            <div class="month-picker-wrapper">
                @include('components.month-picker', ['currentMonth' => $currentMonth])
            </div>

            <div class="entries-summary">
                <div class="summary-card summary-card--income">
                    <div class="summary-card__icon">
                        <i class="bi bi-arrow-down-left"></i>
                    </div>
                    <div class="summary-card__content">
                        <span class="summary-card__label">Income</span>
                        <span class="summary-card__value">{{ $fmt->currency($totalIncome) }}</span>
                    </div>
                </div>
                <div class="summary-card summary-card--expense">
                    <div class="summary-card__icon">
                        <i class="bi bi-arrow-up-right"></i>
                    </div>
                    <div class="summary-card__content">
                        <span class="summary-card__label">Expense</span>
                        <span class="summary-card__value">{{ $fmt->currency($totalExpense) }}</span>
                    </div>
                </div>
            </div>

            <div class="entries-filter">
                <div class="filter-tabs">
                    @php
                        $baseUrl = request()->url();
                        $monthParam = request('month', now()->format('Y-m'));
                        $filters = [
                            'all' => ['label' => 'All', 'icon' => 'bi-grid-3x3-gap'],
                            'income' => ['label' => 'Income', 'icon' => 'bi-arrow-down-left'],
                            'expense' => ['label' => 'Expense', 'icon' => 'bi-arrow-up-right'],
                            'transfer' => ['label' => 'Transfer', 'icon' => 'bi-arrow-left-right'],
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

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="list-wrapper">
            @forelse($events as $event)
                <div class="list-item">
                    @include('components.entry-item', ['event' => $event])
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1"></i>
                    <p>No events for this month</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
