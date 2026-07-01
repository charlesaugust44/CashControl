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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const activeTab = document.querySelector('.filter-tab--active');
        if (activeTab) {
            const container = activeTab.closest('.filter-tabs');
            if (container) {
                const containerRect = container.getBoundingClientRect();
                const tabRect = activeTab.getBoundingClientRect();
                
                const isFullyVisible = tabRect.left >= containerRect.left && 
                                      tabRect.right <= containerRect.right;
                
                if (!isFullyVisible) {
                    activeTab.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'nearest', 
                        inline: 'center' 
                    });
                }
            }
        }
    });
</script>
