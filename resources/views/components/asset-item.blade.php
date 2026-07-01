<a href="{{ url('/assets/' . $asset->id) }}" class="asset-card accent-{{ $fmt->signal($asset->balance) }}">
    <div class="asset-header-row">
        <div class="asset-title">
            <div class="asset-icon-wrapper">
                <i class="bi bi-wallet2"></i>
            </div>
            <h3 class="asset-name">{{ $asset->name ?: 'Unnamed Asset' }}</h3>
        </div>
        <span class="asset-balance balance-{{ $fmt->signal($asset->balance) }}">
            {{ $fmt->currency($asset->balance) }}
        </span>
    </div>
    <div class="asset-details">
        <div class="asset-detail-item">
            <i class="bi bi-calendar-check"></i>
            <span class="detail-label">{{ __('assets.fields.closed_up_to') }}</span>
            <span class="detail-value">
                @if($asset->closed_up_to)
                    {{ $asset->closed_up_to->format('M Y') }}
                @else
                    {{ __('assets.fields.not_closed') }}
                @endif
            </span>
        </div>
        <div class="asset-detail-item">
            <i class="bi bi-clock-history"></i>
            <span class="detail-value">{{ $fmt->dateTime($asset->updated_at) }}</span>
        </div>
    </div>
</a>
