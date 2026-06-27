@php
    $type = $event->header->type?->value ?? 'event';
    $typeIcons = ['income' => 'bi-arrow-down-left', 'expense' => 'bi-arrow-up-right', 'transfer' => 'bi-arrow-left-right'];
    $typeIcon = $typeIcons[$type] ?? 'bi-tag';
    $isVirtual = $event->id === 0 || $event->id === null;
    $isConsolidated = $event->consolidated ?? false;
@endphp

<div class="event-card {{ $isVirtual ? 'virtual' : '' }} {{ $isConsolidated ? 'consolidated' : '' }}">
    <div class="event-header">
        <h3 class="event-name">
            <i class="{{ $typeIcon }}"></i>
            {{ $event->header->name ?? 'Unnamed Event' }}
            @if($isVirtual)
                <span class="badge bg-info">Forecast</span>
            @elseif($isConsolidated)
                <span class="badge bg-success">Consolidated</span>
            @else
                <span class="badge bg-warning">Pending</span>
            @endif
        </h3>
        <span class="event-type {{ $type }}">
            {{ $type }}
        </span>
    </div>
    <div class="event-date">
        <i class="bi bi-calendar3"></i>
        {{ $fmt->date($event->date) }}
    </div>
    <div class="event-entries">
        @foreach($event->entries as $entry)
            <div class="entry-item">
                <span class="entry-asset">
                    <i class="bi bi-wallet2"></i>
                    {{ $entry->asset->name ?? 'Unknown' }}
                </span>
                <span class="entry-amount {{ $fmt->signal($entry->amount) }}">
                    {{ $fmt->currency($entry->amount) }}
                </span>
            </div>
        @endforeach
    </div>
    @if($event->note)
        <div class="event-note">
            {{ $event->note }}
        </div>
    @endif
    @if(!$isVirtual && !$isConsolidated)
        <div class="event-actions">
            <form action="{{ url('/events/' . $event->id . '/consolidate') }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="bi bi-check-circle"></i> Consolidate
                </button>
            </form>
        </div>
    @endif
</div>
