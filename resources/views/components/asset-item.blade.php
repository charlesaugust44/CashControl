<a href="{{ url('/assets/' . $asset->id) }}" class="asset-card">
    <div class="asset-header">
        <div class="asset-title">
            <i class="bi bi-wallet2 asset-icon"></i>
            <h3 class="asset-name">{{ $asset->name ?: 'Unnamed Asset' }}</h3>
        </div>
        <span class="asset-balance balance-{{ $fmt->signal($asset->balance) }}">
            {{ $fmt->currency($asset->balance) }}
        </span>
    </div>
    <div class="asset-details">
        <div class="asset-detail-item">
            <i class="bi bi-calendar-check"></i>
            <span class="detail-label">Consolidation:</span>
            <span class="detail-value">{{ $fmt->date($asset->consolidation) }}</span>
        </div>
        <div class="asset-detail-item">
            <i class="bi bi-clock-history"></i>
            <span class="detail-label">Last updated:</span>
            <span class="detail-value">{{ $fmt->dateTime($asset->updated_at) }}</span>
        </div>
    </div>
</a>
