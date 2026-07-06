@php
    $type = $event->type?->value ?? 'event';
    $typeIcon = $event->type?->icon() ?? 'bi-tag';
    $isVirtual = $event->id === 0 || $event->id === null;
    $isConsolidated = $event->consolidated ?? false;
    $isTransferConsolidated = $event->transfer_consolidated ?? false;
    $isFullyConsolidated = $event->isFullyConsolidated();
    $isPartiallyConsolidated = $event->isPartiallyConsolidated();
    $detailUrl = $event->detailUrl();
@endphp

<a href="{{ $detailUrl }}" class="event-card-link">
    <div class="event-card {{ $isVirtual ? 'virtual' : '' }} {{ $isFullyConsolidated ? 'consolidated' : '' }}">
        <div class="event-header">
            <h3 class="event-name">
                <i class="{{ $typeIcon }}"></i>
                {{ $event->name ?? 'Unnamed Event' }}
                @if($isVirtual)
                    <span class="event-badge event-badge--forecast">
                        <span class="event-badge__dot"></span>
                        {{ __('entries.status.forecast') }}
                    </span>
                @elseif($isFullyConsolidated)
                    <span class="event-badge event-badge--consolidated">
                        <span class="event-badge__dot"></span>
                        {{ __('entries.status.consolidated') }}
                    </span>
                @elseif($isPartiallyConsolidated)
                    <span class="event-badge event-badge--partial">
                        <span class="event-badge__dot"></span>
                        {{ __('entries.status.partial') }}
                    </span>
                @else
                    <span class="event-badge event-badge--pending">
                        <span class="event-badge__dot"></span>
                        {{ __('entries.status.pending') }}
                    </span>
                @endif
            </h3>
            <span class="event-type {{ $type }}">
                {{ __('entries.event_types.' . $type) }}
            </span>
        </div>
        <div class="event-date">
            <i class="bi bi-calendar3"></i>
            {{ $fmt->month($event->date) }}
            @if($event->due_day)
                <span class="event-due-date">
                    <i class="bi bi-bell"></i>
                    {{ $event->due_day }}
                </span>
            @endif
        </div>
        <div class="event-entries">
            @if($event->isTransfer())
                @php
                    $sourceEntry = $event->getSourceEntry();
                    $destEntry = $event->getDestEntry();
                @endphp
                <div class="entry-item transfer-item">
                    <span class="entry-asset">
                        <i class="bi bi-arrow-right"></i>
                        {{ $sourceEntry->asset->name ?? 'Unknown' }} → {{ $destEntry->asset->name ?? 'Unknown' }}
                    </span>
                    <span class="entry-amount amount-transfer">
                        {{ $fmt->currency(abs($destEntry->amount ?? 0)) }}
                    </span>
                </div>
            @elseif($event->isExpenseWithTransfer())
                @php
                    $sourceEntry = $event->getSourceEntry();
                    $destTransferEntry = $event->getDestEntry();
                    $expenseEntry = $event->getExpenseEntry();
                    $amount = $event->getTransferAmount();
                @endphp
                <div class="entry-item transfer-item">
                    <span class="entry-asset">
                        <i class="bi bi-arrow-right"></i>
                        {{ $sourceEntry->asset->name ?? 'Unknown' }} → {{ $destTransferEntry->asset->name ?? 'Unknown' }}
                    </span>
                    <span class="entry-amount amount-transfer">
                        {{ $fmt->currency($amount) }}
                    </span>
                </div>
                <div class="entry-item">
                    <span class="entry-asset">
                        <i class="bi bi-cart-plus"></i>
                        {{ $expenseEntry->asset->name ?? 'Unknown' }}
                    </span>
                    <span class="entry-amount amount-negative">
                        -{{ $fmt->currency($amount) }}
                    </span>
                </div>
            @elseif($event->isIncomeWithTransfer())
                @php
                    $incomeEntry = $event->getIncomeEntry();
                    $destTransferEntry = $event->getLastPositiveEntry();
                    $amount = abs($incomeEntry?->amount ?? 0);
                @endphp
                <div class="entry-item">
                    <span class="entry-asset">
                        <i class="bi bi-cash-coin"></i>
                        {{ $incomeEntry->asset->name ?? 'Unknown' }}
                    </span>
                    <span class="entry-amount amount-positive">
                        +{{ $fmt->currency($amount) }}
                    </span>
                </div>
                <div class="entry-item transfer-item">
                    <span class="entry-asset">
                        <i class="bi bi-arrow-right"></i>
                        {{ $incomeEntry->asset->name ?? 'Unknown' }} → {{ $destTransferEntry->asset->name ?? 'Unknown' }}
                    </span>
                    <span class="entry-amount amount-transfer">
                        {{ $fmt->currency($amount) }}
                    </span>
                </div>
            @else
                @foreach($event->entries as $entry)
                    <div class="entry-item">
                        <span class="entry-asset">
                            <i class="bi bi-wallet2"></i>
                            {{ $entry->asset->name ?? 'Unknown' }}
                        </span>
                        <span class="entry-amount amount-{{ $fmt->signal($entry->amount) }}">
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
