@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/event-detail.css'])
@endpush

@section('content')
    @php
        $type = $event->header->type?->value ?? 'event';
        $typeIcons = ['income' => 'bi-arrow-down-left', 'expense' => 'bi-arrow-up-right', 'transfer' => 'bi-arrow-left-right'];
        $typeIcon = $typeIcons[$type] ?? 'bi-tag';
        $isConsolidated = $event->consolidated ?? false;
        $isTransfer = $event->header->isTransfer();

        $formAction = $isVirtual
            ? url('/entries/virtual/' . $headerId . '/' . $year . '/' . $month)
            : url('/entries/' . $event->id);
        $formMethod = $isVirtual ? 'POST' : 'PUT';
    @endphp

    <div class="event-detail-wrapper">
        <div class="event-detail-card">
            <div class="event-detail-header">
                <div class="event-detail-title">
                    <i class="{{ $typeIcon }}"></i>
                    <h2>{{ $event->header->name ?? 'Event Details' }}</h2>
                </div>
                <div class="event-detail-badges">
                    @if($isVirtual)
                        <span class="badge bg-info">Forecast</span>
                    @elseif($isConsolidated)
                        <span class="badge bg-success">Consolidated</span>
                    @else
                        <span class="badge bg-warning">Pending</span>
                    @endif
                    <span class="event-type-badge {{ $type }}">{{ $type }}</span>
                </div>
            </div>

            <div class="event-detail-date">
                <i class="bi bi-calendar3"></i>
                {{ $fmt->date($event->date) }}
            </div>

            <form method="POST" action="{{ $formAction }}" class="event-detail-form" id="eventForm">
                @csrf
                @if(!$isVirtual)
                    @method($formMethod)
                @endif

                <div class="event-entries-section">
                    <h3 class="section-title">Entries</h3>

                    @if($isTransfer)
                        <div class="transfer-info">
                            <i class="bi bi-info-circle"></i>
                            <span>Transfers move money between two assets. The total amount is the same for both.</span>
                        </div>
                    @endif

                    <div class="entries-list" id="entriesList">
                        @foreach($event->entries as $index => $entry)
                            @php
                                $isSource = $entry->amount < 0;
                                $absoluteAmount = abs($entry->amount);
                            @endphp
                            <div class="entry-row" data-index="{{ $index }}">
                                <div class="entry-row-header">
                                    <span class="entry-label">
                                        @if($isTransfer)
                                            {{ $isSource ? 'From' : 'To' }}
                                        @else
                                            Asset
                                        @endif
                                    </span>
                                    @if(!$isVirtual && !$isConsolidated && count($event->entries) > 1)
                                        <button type="button" class="btn-remove-entry" onclick="removeEntry({{ $index }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>

                                <div class="entry-fields">
                                    <div class="form-group">
                                        <select name="entries[{{ $index }}][asset_id]" class="form-control" {{ $isConsolidated ? 'disabled' : 'required' }}>
                                            <option value="">Select asset</option>
                                            @foreach($assets as $asset)
                                                <option value="{{ $asset->id }}" {{ $entry->asset_id == $asset->id ? 'selected' : '' }}>
                                                    {{ $asset->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <div class="amount-input-wrapper">
                                            <span class="currency-symbol">$</span>
                                            <input
                                                type="number"
                                                name="entries[{{ $index }}][amount]"
                                                class="form-control entry-amount-input"
                                                value="{{ $absoluteAmount }}"
                                                step="0.01"
                                                min="0"
                                                {{ $isConsolidated ? 'disabled' : 'required' }}
                                                data-original-amount="{{ $entry->amount }}"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(!$isConsolidated && !$isTransfer)
                        <button type="button" class="btn-add-entry" onclick="addEntry()">
                            <i class="bi bi-plus-circle"></i>
                            <span>Add Entry</span>
                        </button>
                    @endif
                </div>

                <div class="event-note-section">
                    <label for="note" class="form-label">Note (optional)</label>
                    <textarea
                        name="note"
                        id="note"
                        class="form-control"
                        rows="3"
                        placeholder="Add a note..."
                        {{ $isConsolidated ? 'disabled' : '' }}
                    >{{ old('note', $event->note ?? '') }}</textarea>
                </div>
            </form>

            <div class="event-detail-actions">
                <a href="{{ url('/entries?month=' . \Carbon\Carbon::parse($event->date)->format('Y-m')) }}" class="btn btn-outline-secondary">
                    Cancel
                </a>

                @if(!$isVirtual && !$isConsolidated)
                    <button type="button" class="btn btn-danger" onclick="deleteEvent()">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                @endif

                @if(!$isConsolidated)
                    <button type="submit" name="action" value="save" form="eventForm" class="btn btn-primary">
                        <i class="bi bi-save"></i>
                        Save
                    </button>
                    <button type="submit" name="action" value="submit" form="eventForm" class="btn btn-secondary">
                        <i class="bi bi-check-circle"></i>
                        Submit
                    </button>
                    @if(!$isVirtual)
                        <button type="submit" name="action" value="consolidate" form="eventForm" class="btn btn-success">
                            <i class="bi bi-check-all"></i> Consolidate
                        </button>
                    @endif
                @endif

                @if(!$isVirtual && $isConsolidated)
                    <button type="submit" name="action" value="unconsolidate" form="eventForm" class="btn btn-warning">
                        <i class="bi bi-arrow-counterclockwise"></i> Unconsolidate
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if(!$isConsolidated)
        <template id="entryTemplate">
            <div class="entry-row" data-index="__INDEX__">
                <div class="entry-row-header">
                    <span class="entry-label">Asset</span>
                    <button type="button" class="btn-remove-entry" onclick="removeEntry(__INDEX__)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="entry-fields">
                    <div class="form-group">
                        <select name="entries[__INDEX__][asset_id]" class="form-control" required>
                            <option value="">Select asset</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="amount-input-wrapper">
                            <span class="currency-symbol">$</span>
                            <input
                                type="number"
                                name="entries[__INDEX__][amount]"
                                class="form-control entry-amount-input"
                                step="0.01"
                                min="0"
                                required
                            >
                        </div>
                    </div>
                </div>
            </div>
        </template>
    @endif
@endsection

@push('scripts')
    <script>
        let entryIndex = {{ $event->entries->count() }};

        function addEntry() {
            const template = document.getElementById('entryTemplate');
            const html = template.innerHTML.replace(/__INDEX__/g, entryIndex);
            document.getElementById('entriesList').insertAdjacentHTML('beforeend', html);
            entryIndex++;
        }

        function removeEntry(index) {
            const row = document.querySelector(`.entry-row[data-index="${index}"]`);
            if (row) {
                row.remove();
            }
        }

        function deleteEvent() {
            if (confirm('Are you sure you want to delete this event?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url('/entries/' . $event->id) }}';

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.entry-amount-input').forEach(input => {
                input.addEventListener('change', function() {
                    const originalAmount = parseFloat(this.dataset.originalAmount);
                    const newAmount = parseFloat(this.value);
                    const isNegative = originalAmount < 0;

                    if (isNegative && newAmount > 0) {
                        this.dataset.originalAmount = -newAmount;
                    } else {
                        this.dataset.originalAmount = newAmount;
                    }
                });
            });
        });
    </script>
@endpush
