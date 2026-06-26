<div class="event-card">
    <div class="event-header">
        <h3 class="event-name">
            <i class="bi bi-tag"></i>
            {{ $event->header->name ?? 'Unnamed Event' }}
        </h3>
        <span class="event-type {{ $event->header->type ?? '' }}">
            {{ $event->header->type ?? 'event' }}
        </span>
    </div>
    <div class="event-date">
        {{ $fmt->date($event->date) }}
    </div>
    <div class="event-entries">
        @foreach($event->entries as $entry)
            <div class="entry-item">
                <span class="entry-asset">
                    <i class="bi bi-wallet2"></i>
                    {{ $entry->asset->name }}
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
</div>
