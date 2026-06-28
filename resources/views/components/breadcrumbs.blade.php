@if(isset($breadcrumbs) && count($breadcrumbs) >= 2)
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            @foreach($breadcrumbs as $index => $crumb)
                <li class="breadcrumb-item {{ $index === count($breadcrumbs) - 1 ? 'active' : '' }}">
                    @if($index === count($breadcrumbs) - 1)
                        <span>{{ $crumb['label'] }}</span>
                    @else
                        <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
