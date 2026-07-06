@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/dashboard.js'])
@endpush

@section('content')
    @php
        $currentMonthLabel = $monthDate->translatedFormat('M Y');
        $nextMonthLabel = $monthDate->copy()->addMonth()->translatedFormat('M Y');
    @endphp

    <div class="dashboard-grid">
        <x-dashboard-card size="{{ $isMonthClosed ? 'full' : 'lg' }}">
            <div class="dashboard-stats">
                <div class="dashboard-stats__section-label">
                    {{ __('dashboard.consolidated') }}
                </div>
                <div class="dashboard-stats__row">
                    <div class="dashboard-stat">
                        <div class="dashboard-stat__label">
                            <i class="bi bi-arrow-down-left"></i>
                            {{ __('dashboard.income') }}
                        </div>
                        <div class="dashboard-stat__value dashboard-stat__value--success">{{ $fmt->currency($consolidatedIncome) }}</div>
                    </div>

                    <div class="dashboard-stat">
                        <div class="dashboard-stat__label">
                            <i class="bi bi-arrow-up-right"></i>
                            {{ __('dashboard.expenses') }}
                        </div>
                        <div class="dashboard-stat__value dashboard-stat__value--danger">{{ $fmt->currency($consolidatedExpense) }}</div>
                    </div>

                    <div class="dashboard-stat">
                        <div class="dashboard-stat__label">
                            <i class="bi bi-wallet2"></i>
                            {{ __('dashboard.total_assets') }}
                        </div>
                        <div class="dashboard-stat__value">{{ $fmt->currency($totalBalance) }}</div>
                    </div>
                </div>
            </div>
        </x-dashboard-card>

        @if(!$isMonthClosed)
        <x-dashboard-card size="lg">
            <div class="dashboard-stats">
                <div class="dashboard-stats__section-label">
                    {{ __('dashboard.forecasted') }} ({{ $currentMonthLabel }})
                </div>
                <div class="dashboard-stats__row">
                    <div class="dashboard-stat">
                        <div class="dashboard-stat__label">
                            <i class="bi bi-arrow-down-left"></i>
                            {{ __('dashboard.income') }}

                            @if($incomeTrend)
                                <div class="dashboard-stat__trend dashboard-stat__trend--{{ $incomeTrendDir }}">
                                    <i class="bi {{ $incomeTrendDir === 'down' ? 'bi-arrow-down-short' : 'bi-arrow-up-short' }}"></i>
                                    {{ $incomeTrend }}
                                </div>
                            @endif
                        </div>
                        <div class="dashboard-stat__value dashboard-stat__value--success">{{ $fmt->currency($forecastedIncome) }}</div>
                    </div>

                    <div class="dashboard-stat">
                        <div class="dashboard-stat__label">
                            <i class="bi bi-arrow-up-right"></i>
                            {{ __('dashboard.expenses') }}

                            @if($expenseTrend)
                                <div class="dashboard-stat__trend dashboard-stat__trend--{{ $expenseTrendDir }}">
                                    <i class="bi {{ $expenseTrendDir === 'down' ? 'bi-arrow-down-short' : 'bi-arrow-up-short' }}"></i>
                                    {{ $expenseTrend }}
                                </div>
                            @endif
                        </div>
                        <div class="dashboard-stat__value dashboard-stat__value--danger">{{ $fmt->currency($forecastedExpense) }}</div>
                    </div>

                    <div class="dashboard-stat">
                        <div class="dashboard-stat__label">
                            <i class="bi bi-graph-up-arrow"></i>
                            {{ __('dashboard.forecasted_total') }}
                        </div>
                        <div class="dashboard-stat__value">{{ $fmt->currency($forecastBalance) }}</div>
                    </div>
                </div>
            </div>
        </x-dashboard-card>
        @endif

        <x-dashboard-card size="full" title="{{ __('dashboard.pending_consolidations') }}" icon="bi-clock-history" :scrollable="true">
            @slot('actions')
                @include('components.filter-tabs', [
                    'typeFilter' => $pendingTypeFilter,
                    'assetFilter' => $pendingAssetFilter,
                    'typeOptions' => [
                        'income'                => ['label' => __('templates.types.income'),           'icon' => 'bi-arrow-down-left'],
                        'expense'               => ['label' => __('templates.types.expense'),          'icon' => 'bi-arrow-up-right'],
                        'transfer'              => ['label' => __('templates.types.transfer'),         'icon' => 'bi-arrow-left-right'],
                        'expense_with_transfer' => ['label' => __('templates.types.expense_with_transfer'), 'icon' => 'bi-cart-plus'],
                        'income_with_transfer'  => ['label' => __('templates.types.income_with_transfer'),  'icon' => 'bi-cash-coin'],
                    ],
                    'assetOptions' => $assets->mapWithKeys(fn ($a) => [$a->id => ['label' => $a->name]])->toArray(),
                    'showConsolidated' => false,
                    'queryParams' => ['type' => 'pending_type', 'consolidated' => 'pending_consolidated', 'asset' => 'pending_asset'],
                ])
            @endslot
            @if($pendingConsolidations->count() > 0)
                <ul class="pending-list">
                    @foreach($pendingConsolidations as $event)
                        @php
                            $type = $event->type?->value ?? 'event';
                            $typeIcon = $event->type?->icon() ?? 'bi-tag';
                            $isTransfer = $event->isTransfer();
                            $total = $event->getDisplayAmount();
                            $isPartiallyConsolidated = method_exists($event, 'isPartiallyConsolidated') && $event->isPartiallyConsolidated();
                            $detailUrl = $event->detailUrl();
                        @endphp
                        <li class="pending-item">
                            <a href="{{ $detailUrl }}" class="pending-item__info" style="text-decoration: none; color: inherit;">
                                <i class="bi {{ $typeIcon }} pending-item__icon"></i>
                                <div class="pending-item__details">
                                    <span class="pending-item__name">
                                        {{ $event->name ?? __('ui.none') }}
                                        @if($isPartiallyConsolidated)
                                            <span class="event-badge event-badge--partial" style="font-size: 0.7em; vertical-align: middle;">
                                                <span class="event-badge__dot"></span>
                                                {{ __('entries.status.partial') }}
                                            </span>
                                        @endif
                                    </span>
                                    <span class="pending-item__date">
                                        @if($event->due_day)
                                            {{ $event->due_day . ' ' . $fmt->month($event->date)  }}
                                        @else
                                            {{ $fmt->month($event->date) }}
                                        @endif
                                    </span>
                                </div>
                            </a>
                            <span class="pending-item__amount amount-{{ $isTransfer ? 'transfer' : $fmt->signal($total) }}">
                                {{ $fmt->currency($total) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="empty-widget">
                    <i class="bi bi-check-circle" style="margin-right: var(--space-2)"></i>
                    {{ __('dashboard.no_pending') }}
                </div>
            @endif
        </x-dashboard-card>

        <div class="dashboard-charts-row">
            <x-dashboard-card size="lg" title="{{ __('dashboard.balance_history') }}" icon="bi-graph-up">
                <div class="dashboard-chart">
                    <canvas id="balanceHistoryChart" data-chart="{{ json_encode(array_merge($balanceHistory, ['label' => __('dashboard.total_balance')])) }}"></canvas>
                </div>
            </x-dashboard-card>

            <x-dashboard-card size="lg" title="{{ __('dashboard.income_vs_expenses') }}" icon="bi-bar-chart">
                <div class="dashboard-chart">
                    <canvas id="incomeVsExpensesChart" data-chart="{{ json_encode(array_merge($monthlyBreakdown, ['incomeLabel' => __('dashboard.income'), 'expenseLabel' => __('dashboard.expenses')])) }}"></canvas>
                </div>
            </x-dashboard-card>
        </div>
    </div>
@endsection
