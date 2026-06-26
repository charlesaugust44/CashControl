<div class="asset-header">
    <div class="assets-total">
        <div class="assets-total-icon">
            <i class="bi bi-wallet2"></i>
        </div>
        <div class="total-info">
            <span class="total-label">Total Assets</span>
            <span class="total-amount balance-{{ $fmt->signal($total) }}">
                {{ $fmt->currency($total) }}
            </span>
        </div>
    </div>
    <a href="{{ $actionUrl }}" class="add-asset-btn">
        {{ $slot }}
    </a>
</div>
