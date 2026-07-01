@php
    $filters = $filters ?? [];
    $currentFilter = $currentFilter ?? 'all';
    $queryParam = $queryParam ?? 'filter';
@endphp

<div class="filter-tabs-wrapper">
    <div class="filter-tabs">
        @foreach($filters as $key => $filterConfig)
            <a href="{{ request()->fullUrlWithQuery([$queryParam => $key]) }}"
               class="filter-tab {{ $currentFilter === $key ? 'filter-tab--active' : '' }}">
                <i class="bi {{ $filterConfig['icon'] }}"></i>
                <span>{{ $filterConfig['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
