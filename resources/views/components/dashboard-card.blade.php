@php
    $title = $title ?? '';
    $icon = $icon ?? 'bi-bar-chart';
    $size = $size ?? 'md';
    $scrollable = $scrollable ?? false;
    $sizeClass = match($size) {
        'sm' => 'dashboard-card--sm',
        'md' => 'dashboard-card--md',
        'lg' => 'dashboard-card--lg',
        'full' => 'dashboard-card--full',
        default => 'dashboard-card--md',
    };
    $scrollableClass = $scrollable ? 'dashboard-card--scrollable' : '';
@endphp

<div class="dashboard-card {{ $sizeClass }} {{ $scrollableClass }}">
    @if($title)
        <div class="dashboard-card__header">
            <h3 class="dashboard-card__title">
                <i class="bi {{ $icon }}"></i>
                {{ $title }}
            </h3>
            @if(isset($actions))
                <div class="dashboard-card__actions">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    <div class="dashboard-card__body">
        {{ $slot }}
    </div>
</div>
