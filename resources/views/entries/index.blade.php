@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/entries.css'])
@endpush

@section('content')
    <div class="entries-container">
        <div class="entries-top-bar">
            <div class="summary-panel"
                 data-forecasted-income="{{ $fmt->currency($totalIncome) }}"
                 data-forecasted-expense="{{ $fmt->currency($totalExpense) }}"
                 data-forecasted-balance="{{ $fmt->currency(abs($balance)) }}"
                 data-forecasted-balance-positive="{{ $balance >= 0 ? '1' : '0' }}"
                 data-consolidated-income="{{ $fmt->currency($consolidatedIncome) }}"
                 data-consolidated-expense="{{ $fmt->currency($consolidatedExpense) }}"
                 data-consolidated-balance="{{ $fmt->currency(abs($consolidatedBalance)) }}"
                 data-consolidated-balance-positive="{{ $consolidatedBalance >= 0 ? '1' : '0' }}"
                 data-is-month-closed="{{ $isMonthClosed ? '1' : '0' }}">
                <div class="summary-toggle">
                    @if(!$isMonthClosed)
                    <button type="button" class="summary-toggle__option summary-toggle__option--active" data-mode="forecasted">
                        <i class="bi bi-dash-circle"></i>
                        {{ __('entries.forecasted') }}
                    </button>
                    <button type="button" class="summary-toggle__option" data-mode="consolidated">
                        <i class="bi bi-check-circle"></i>
                        {{ __('entries.consolidated') }}
                    </button>
                    @else
                    <button type="button" class="summary-toggle__option summary-toggle__option--active" data-mode="consolidated">
                        <i class="bi bi-check-circle"></i>
                        {{ __('entries.consolidated') }}
                    </button>
                    @endif
                </div>

                <div class="entries-summary">
                    <div class="summary-card summary-card--income">
                        <div class="summary-card__content">
                            <span class="summary-card__label">
                                <i class="bi bi-arrow-down-left"></i>
                                {{ __('templates.types.income') }}
                            </span>
                            <span class="summary-card__value">{{ $fmt->currency($isMonthClosed ? $consolidatedIncome : $totalIncome) }}</span>
                        </div>
                    </div>
                    <div class="summary-card summary-card--expense">
                        <div class="summary-card__content">
                            <span class="summary-card__label">
                                <i class="bi bi-arrow-up-right"></i>
                                {{ __('templates.types.expense') }}
                            </span>
                            <span class="summary-card__value">{{ $fmt->currency($isMonthClosed ? $consolidatedExpense : $totalExpense) }}</span>
                        </div>
                    </div>
                    <div class="summary-card summary-card--balance {{ ($isMonthClosed ? $consolidatedBalance : $balance) >= 0 ? 'summary-card--positive' : 'summary-card--negative' }}">
                        <div class="summary-card__content">
                            <span class="summary-card__label">
                                <i class="bi bi-{{ ($isMonthClosed ? $consolidatedBalance : $balance) >= 0 ? 'cash' : 'exclamation-circle' }}"></i>
                                {{ __('entries.balance') }}
                            </span>
                            <span class="summary-card__value">{{ $fmt->currency(abs($isMonthClosed ? $consolidatedBalance : $balance)) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @include('components.filter-tabs', [
                'filters' => [
                    'all'                   => ['label' => __('ui.all'),                          'icon' => 'bi-grid-3x3-gap'],
                    'income'                => ['label' => __('templates.types.income'),           'icon' => 'bi-arrow-down-left'],
                    'expense'               => ['label' => __('templates.types.expense'),          'icon' => 'bi-arrow-up-right'],
                    'transfer'              => ['label' => __('templates.types.transfer'),         'icon' => 'bi-arrow-left-right'],
                    'expense_with_transfer' => ['label' => __('templates.types.expense_with_transfer'), 'icon' => 'bi-cart-plus'],
                    'income_with_transfer'  => ['label' => __('templates.types.income_with_transfer'),  'icon' => 'bi-cash-coin'],
                ],
                'currentFilter' => $currentFilter,
            ])
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.querySelector('.summary-toggle');
        const panel = document.querySelector('.summary-panel');

        if (!toggle || !panel) return;

        const isMonthClosed = panel.dataset.isMonthClosed === '1';

        toggle.addEventListener('click', function (e) {
            if (isMonthClosed) return;

            const btn = e.target.closest('.summary-toggle__option');
            if (!btn || btn.classList.contains('summary-toggle__option--active')) return;

            const mode = btn.dataset.mode;
            toggle.querySelectorAll('.summary-toggle__option').forEach(b => b.classList.remove('summary-toggle__option--active'));
            btn.classList.add('summary-toggle__option--active');

            const summary = panel.querySelector('.entries-summary');
            const incomeCard = summary.querySelector('.summary-card--income .summary-card__value');
            const expenseCard = summary.querySelector('.summary-card--expense .summary-card__value');
            const balanceCard = summary.querySelector('.summary-card--balance');
            const balanceValue = balanceCard.querySelector('.summary-card__value');
            const balanceLabel = balanceCard.querySelector('.summary-card__label');
            const balanceIcon = balanceLabel.querySelector('i');

            incomeCard.textContent = panel.dataset[mode + 'Income'];
            expenseCard.textContent = panel.dataset[mode + 'Expense'];
            balanceValue.textContent = panel.dataset[mode + 'Balance'];

            const isPositive = panel.dataset[mode + 'BalancePositive'] === '1';
            balanceCard.classList.toggle('summary-card--positive', isPositive);
            balanceCard.classList.toggle('summary-card--negative', !isPositive);
            balanceIcon.className = isPositive ? 'bi bi-cash' : 'bi bi-exclamation-circle';
        });
    });
</script>
@endpush
