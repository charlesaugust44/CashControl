<div class="asset-header">
    <div class="assets-total">
        <div class="assets-total-icon">
            <i class="bi bi-wallet2"></i>
        </div>
        <div class="total-info">
            <span class="total-label">{{ __('dashboard.total_assets') }}</span>
            <span class="total-amount balance-{{ $fmt->signal($total) }}">
                {{ $fmt->currency($total) }}
            </span>
        </div>
    </div>
    @if(isset($forecastedTotal) && isset($currentMonthLabel))
    <div class="assets-total">
        <div class="assets-total-icon">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="total-info">
            <span class="total-label">{{ __('dashboard.forecasted_total') }} ({{ $currentMonthLabel }})</span>
            <span class="total-amount balance-{{ $fmt->signal($forecastedTotal) }}">
                {{ $fmt->currency($forecastedTotal) }}
            </span>
        </div>
    </div>
    @endif
</div>
