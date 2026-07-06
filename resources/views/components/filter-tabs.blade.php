@php
    $typeFilter = $typeFilter ?? '';
    $consolidatedFilter = $consolidatedFilter ?? '';
    $assetFilter = $assetFilter ?? '';
    $typeOptions = $typeOptions ?? [];
    $consolidatedOptions = $consolidatedOptions ?? [];
    $assetOptions = $assetOptions ?? [];
    $showConsolidated = $showConsolidated ?? true;
    $queryParams = $queryParams ?? ['type' => 'type', 'consolidated' => 'consolidated', 'asset' => 'asset'];
    $hasActiveFilter = !empty($typeFilter) || !empty($consolidatedFilter) || !empty($assetFilter);

    $typeLabel = !empty($typeFilter) && isset($typeOptions[$typeFilter]) ? $typeOptions[$typeFilter]['label'] : __('entries.filters.event_type');
    $consolidatedLabel = !empty($consolidatedFilter) && isset($consolidatedOptions[$consolidatedFilter]) ? $consolidatedOptions[$consolidatedFilter]['label'] : __('entries.filters.consolidated');
    $assetLabel = !empty($assetFilter) && isset($assetOptions[$assetFilter]) ? $assetOptions[$assetFilter]['label'] : __('entries.filters.asset');
@endphp

<div class="filter-tabs-wrapper">
    <div class="filter-tabs">
        <a href="{{ request()->fullUrlWithQuery([$queryParams['type'] => '', $queryParams['consolidated'] => '', $queryParams['asset'] => '']) }}"
           class="filter-tab {{ !$hasActiveFilter ? 'filter-tab--active' : '' }}">
            <i class="bi bi-grid-3x3-gap"></i>
            <span>{{ __('ui.all') }}</span>
        </a>

        <div class="filter-dropdown">
            <button type="button" class="filter-tab filter-tab--dropdown {{ !empty($typeFilter) ? 'filter-tab--active' : '' }}" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-tag"></i>
                <span>{{ $typeLabel }}</span>
                <i class="bi bi-chevron-down filter-tab__chevron"></i>
            </button>
            <ul class="dropdown-menu">
                @foreach($typeOptions as $key => $option)
                    <li>
                        <a class="dropdown-item {{ $typeFilter === $key ? 'active' : '' }}"
                           href="{{ request()->fullUrlWithQuery([$queryParams['type'] => $key]) }}">
                            <i class="bi {{ $option['icon'] }}"></i>
                            <span>{{ $option['label'] }}</span>
                            @if($typeFilter === $key)
                                <i class="bi bi-check dropdown-item__check"></i>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        @if($showConsolidated && count($consolidatedOptions) > 0)
        <div class="filter-dropdown">
            <button type="button" class="filter-tab filter-tab--dropdown {{ !empty($consolidatedFilter) ? 'filter-tab--active' : '' }}" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-check2-circle"></i>
                <span>{{ $consolidatedLabel }}</span>
                <i class="bi bi-chevron-down filter-tab__chevron"></i>
            </button>
            <ul class="dropdown-menu">
                @foreach($consolidatedOptions as $key => $option)
                    <li>
                        <a class="dropdown-item {{ $consolidatedFilter === $key ? 'active' : '' }}"
                           href="{{ request()->fullUrlWithQuery([$queryParams['consolidated'] => $key]) }}">
                            <i class="bi {{ $option['icon'] }}"></i>
                            <span>{{ $option['label'] }}</span>
                            @if($consolidatedFilter === $key)
                                <i class="bi bi-check dropdown-item__check"></i>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(count($assetOptions) > 0)
        <div class="filter-dropdown">
            <button type="button" class="filter-tab filter-tab--dropdown {{ !empty($assetFilter) ? 'filter-tab--active' : '' }}" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-wallet2"></i>
                <span>{{ $assetLabel }}</span>
                <i class="bi bi-chevron-down filter-tab__chevron"></i>
            </button>
            <ul class="dropdown-menu">
                @foreach($assetOptions as $key => $option)
                    <li>
                        <a class="dropdown-item {{ $assetFilter === $key ? 'active' : '' }}"
                           href="{{ request()->fullUrlWithQuery([$queryParams['asset'] => $key]) }}">
                            <span>{{ $option['label'] }}</span>
                            @if($assetFilter === $key)
                                <i class="bi bi-check dropdown-item__check"></i>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
