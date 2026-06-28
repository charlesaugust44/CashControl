@php
    $type = $event->header->type?->value ?? 'event';
    $typeIcons = ['income' => 'bi-arrow-down-left', 'expense' => 'bi-arrow-up-right', 'transfer' => 'bi-arrow-left-right'];
    $typeIcon = $typeIcons[$type] ?? 'bi-tag';
    $isVirtual = $event->id === 0 || $event->id === null;
    $isConsolidated = $event->consolidated ?? false;

    if ($isVirtual) {
        $detailUrl = url('/entries/virtual/' . $event->header_id . '/' . $event->date->format('Y') . '/' . $event->date->format('m'));
    } else {
        $detailUrl = url('/entries/' . $event->id);
    }
@endphp

<a href="{{ $detailUrl }}" class="event-card-link">
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
            @if($event->header->isTransfer())
                @php
                    $sourceEntry = $event->entries->first(fn($e) => $e->amount < 0);
                    $destEntry = $event->entries->first(fn($e) => $e->amount > 0);
                @endphp
                <div class="entry-item transfer-item">
                    <span class="entry-asset">
                        <i class="bi bi-arrow-right"></i>
                        {{ $sourceEntry->asset->name ?? 'Unknown' }} → {{ $destEntry->asset->name ?? 'Unknown' }}
                    </span>
                    <span class="entry-amount positive">
                        {{ $fmt->currency(abs($destEntry->amount ?? 0)) }}
                    </span>
                </div>
            @else
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
            @endif
        </div>
        @if($event->note)
            <div class="event-note">
                {{ $event->note }}
            </div>
        @endif
    </div>
</a>
