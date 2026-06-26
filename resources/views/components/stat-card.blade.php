@php
    $variant = $variant ?? 'default';
    $icon = $icon ?? 'bi-bar-chart';
    $label = $label ?? '';
    $value = $value ?? '';
    $trend = $trend ?? null;
    $trendDirection = $trendDirection ?? null;
@endphp

<div class="stat-card">
    <div class="stat-card__header">
        <div class="stat-card__icon stat-card__icon--{{ $variant }}">
            <i class="bi {{ $icon }}"></i>
        </div>
        <span class="stat-card__label">{{ $label }}</span>
    </div>
    <div class="stat-card__value stat-card__value--{{ $variant }}">
        {{ $value }}
    </div>
    @if($trend)
        <div class="stat-card__trend stat-card__trend--{{ $trendDirection ?? 'up' }}">
            <i class="bi {{ $trendDirection === 'down' ? 'bi-arrow-down-short' : 'bi-arrow-up-short' }}"></i>
            {{ $trend }}
        </div>
    @endif
</div>
