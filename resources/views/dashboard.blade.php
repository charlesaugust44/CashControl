@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/dashboard.js'])
@endpush

@section('content')
    @php
        $now = now();
        $currentMonthLabel = $now->translatedFormat('M Y');
        $nextMonthLabel = $now->copy()->addMonth()->translatedFormat('M Y');
    @endphp

    <div class="dashboard-grid">
        <x-dashboard-card size="lg">
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
                        </div>
                        <div class="dashboard-stat__value dashboard-stat__value--success">{{ $fmt->currency($forecastedIncome) }}</div>
                        @if($incomeTrend)
                            <div class="dashboard-stat__trend dashboard-stat__trend--{{ $incomeTrendDir }}">
                                <i class="bi {{ $incomeTrendDir === 'down' ? 'bi-arrow-down-short' : 'bi-arrow-up-short' }}"></i>
                                {{ $incomeTrend }}
                            </div>
                        @endif
                    </div>

                    <div class="dashboard-stat">
                        <div class="dashboard-stat__label">
                            <i class="bi bi-arrow-up-right"></i>
                            {{ __('dashboard.expenses') }}
                        </div>
                        <div class="dashboard-stat__value dashboard-stat__value--danger">{{ $fmt->currency($forecastedExpense) }}</div>
                        @if($expenseTrend)
                            <div class="dashboard-stat__trend dashboard-stat__trend--{{ $expenseTrendDir }}">
                                <i class="bi {{ $expenseTrendDir === 'down' ? 'bi-arrow-down-short' : 'bi-arrow-up-short' }}"></i>
                                {{ $expenseTrend }}
                            </div>
                        @endif
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

        <x-dashboard-card size="full" title="{{ __('dashboard.pending_consolidations') }}" icon="bi-clock-history">
            @if($pendingConsolidations->count() > 0)
                <ul class="pending-list">
                    @foreach($pendingConsolidations as $event)
                        @php
                            $type = $event->type?->value ?? 'event';
                            $typeIcons = ['income' => 'bi-arrow-down-left', 'expense' => 'bi-arrow-up-right', 'transfer' => 'bi-arrow-left-right', 'expense_with_transfer' => 'bi-cart-plus'];
                            $typeIcon = $typeIcons[$type] ?? 'bi-tag';
                            $isTransfer = $type === 'transfer';
                            $total = $isTransfer
                                ? abs($event->entries->first(fn($e) => $e->amount > 0)?->amount ?? 0)
                                : $event->entries->sum('amount');
                            $isVirtual = $event->id === 0 || $event->id === null;
                            if ($isVirtual) {
                                $detailUrl = url('/entries/virtual/' . $event->header_id . '/' . $event->date->format('Y') . '/' . $event->date->format('m'));
                            } else {
                                $detailUrl = url('/entries/' . $event->id);
                            }
                        @endphp
                        <li class="pending-item">
                            <a href="{{ $detailUrl }}" class="pending-item__info" style="text-decoration: none; color: inherit;">
                                <i class="bi {{ $typeIcon }} pending-item__icon"></i>
                                <div class="pending-item__details">
                                    <span class="pending-item__name">{{ $event->name ?? __('ui.none') }}</span>
                                    <span class="pending-item__date">{{ $fmt->date($event->date) }}</span>
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
