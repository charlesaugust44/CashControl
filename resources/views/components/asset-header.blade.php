<div class="asset-header">
    <a href="{{ $actionUrl }}" class="add-asset-btn">
        {{ $slot }}
    </a>
    <div class="assets-total">
        <i class="bi bi-wallet2"></i>
        <div class="total-info">
            <span class="total-label">Total Assets</span>
            <span class="total-amount {{ $fmt->signal($total) }}">
                {{ $fmt->currency($total) }}
            </span>
        </div>
    </div>
</div>
