@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/event-detail.css'])
@endpush

@section('content')
    @php
        $type = $event->type?->value ?? 'event';
        $typeIcons = ['income' => 'bi-arrow-down-left', 'expense' => 'bi-arrow-up-right', 'transfer' => 'bi-arrow-left-right', 'expense_with_transfer' => 'bi-cart-plus'];
        $typeIcon = $typeIcons[$type] ?? 'bi-tag';
        $isConsolidated = $event->consolidated ?? false;
        $isTransfer = $event->isTransfer();
        $isExpenseWithTransfer = $event->isExpenseWithTransfer();

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
                    <h2>{{ $event->name ?? 'Event Details' }}</h2>
                </div>
                <div class="event-detail-badges">
                    @if($isVirtual)
                        <span class="badge bg-info">Forecast</span>
                    @elseif($isConsolidated)
                        <span class="badge bg-success">Consolidated</span>
                    @else
                        <span class="badge bg-warning">Pending</span>
                    @endif
                    <span class="event-type-badge {{ $type }}">{{ __('entries.event_types.' . $type) }}</span>
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

                    <div class="entries-list" id="entriesList">
                        @if($isTransfer)
                            @php
                                $sourceEntry = $event->entries->first(fn($e) => $e->amount < 0);
                                $destEntry = $event->entries->first(fn($e) => $e->amount > 0);
                                $transferAmount = abs($sourceEntry->amount ?? 0);
                            @endphp
                            
                            <div class="transfer-amount-section">
                                <div class="form-group">
                                    <label class="form-label">Transfer Amount</label>
                                    <div class="amount-input-wrapper">
                                        <span class="currency-symbol">{{ $fmt->currencySymbol() }}</span>
                                        <input
                                            type="number"
                                            name="transfer_amount"
                                            id="transferAmount"
                                            class="form-control"
                                            value="{{ $transferAmount }}"
                                            step="0.01"
                                            min="0"
                                            {{ $isConsolidated ? 'disabled' : 'required' }}
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="transfer-assets-row">
                                <div class="form-group">
                                    <label class="form-label">From</label>
                                    <select name="entries[0][asset_id]" class="form-control" {{ $isConsolidated ? 'disabled' : 'required' }}>
                                        <option value="">Select source asset</option>
                                        @foreach($assets as $asset)
                                            <option value="{{ $asset->id }}" {{ $sourceEntry && $sourceEntry->asset_id == $asset->id ? 'selected' : '' }}>
                                                {{ $asset->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="entries[0][amount]" value="-{{ $transferAmount }}">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">To</label>
                                    <select name="entries[1][asset_id]" class="form-control" {{ $isConsolidated ? 'disabled' : 'required' }}>
                                        <option value="">Select destination asset</option>
                                        @foreach($assets as $asset)
                                            <option value="{{ $asset->id }}" {{ $destEntry && $destEntry->asset_id == $asset->id ? 'selected' : '' }}>
                                                {{ $asset->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="entries[1][amount]" value="{{ $transferAmount }}">
                                </div>
                            </div>
                        @elseif($isExpenseWithTransfer)
                            @php
                                $sourceEntry = $event->entries->first(fn($e) => $e->amount < 0);
                                $destTransferEntry = $event->entries->first(fn($e) => $e->amount > 0);
                                $amount = abs($destTransferEntry->amount ?? 0);
                            @endphp

                            <div class="transfer-amount-section">
                                <div class="form-group">
                                    <label class="form-label">Amount</label>
                                    <div class="amount-input-wrapper">
                                        <span class="currency-symbol">{{ $fmt->currencySymbol() }}</span>
                                        <input
                                            type="number"
                                            name="expense_transfer_amount"
                                            id="expenseTransferAmount"
                                            class="form-control"
                                            value="{{ $amount }}"
                                            step="0.01"
                                            min="0"
                                            {{ $isConsolidated ? 'disabled' : 'required' }}
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="transfer-assets-row">
                                <div class="form-group">
                                    <label class="form-label">From (Source)</label>
                                    <select name="entries[0][asset_id]" id="ewtSourceAsset" class="form-control" {{ $isConsolidated ? 'disabled' : 'required' }}>
                                        <option value="">Select source asset</option>
                                        @foreach($assets as $asset)
                                            <option value="{{ $asset->id }}" {{ $sourceEntry && $sourceEntry->asset_id == $asset->id ? 'selected' : '' }}>
                                                {{ $asset->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="entries[0][amount]" id="ewtSourceAmount" value="-{{ $amount }}">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">To / Expense Asset</label>
                                    <select name="entries[1][asset_id]" id="ewtDestAsset" class="form-control" {{ $isConsolidated ? 'disabled' : 'required' }}>
                                        <option value="">Select destination asset</option>
                                        @foreach($assets as $asset)
                                            <option value="{{ $asset->id }}" {{ $destTransferEntry && $destTransferEntry->asset_id == $asset->id ? 'selected' : '' }}>
                                                {{ $asset->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="entries[1][amount]" id="ewtDestTransferAmount" value="{{ $amount }}">
                                    <input type="hidden" name="entries[2][asset_id]" id="ewtExpenseAssetId" value="{{ $destTransferEntry->asset_id ?? '' }}">
                                    <input type="hidden" name="entries[2][amount]" id="ewtExpenseAmount" value="-{{ $amount }}">
                                </div>
                            </div>
                        @else
                            @foreach($event->entries as $index => $entry)
                                @php
                                    $absoluteAmount = abs($entry->amount);
                                @endphp
                                <div class="entry-row" data-index="{{ $index }}">
                                    <div class="entry-row-header">
                                        <span class="entry-label">Asset</span>
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
                                        <span class="currency-symbol">{{ $fmt->currencySymbol() }}</span>
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
                        @endif
                    </div>

                    @if(!$isConsolidated && !$isTransfer && !$isExpenseWithTransfer)
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
                    <button type="submit" name="action" value="consolidate" form="eventForm" class="btn btn-success">
                        <i class="bi bi-check-all"></i> Consolidate
                    </button>
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
                            <span class="currency-symbol">{{ $fmt->currencySymbol() }}</span>
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

            // Handle transfer amount sync
            const transferAmountInput = document.getElementById('transferAmount');
            if (transferAmountInput) {
                transferAmountInput.addEventListener('input', function() {
                    const amount = parseFloat(this.value) || 0;
                    const sourceAmountInput = document.querySelector('input[name="entries[0][amount]"]');
                    const destAmountInput = document.querySelector('input[name="entries[1][amount]"]');
                    
                    if (sourceAmountInput) {
                        sourceAmountInput.value = -amount;
                    }
                    if (destAmountInput) {
                        destAmountInput.value = amount;
                    }
                });
            }

            // Handle expense with transfer amount sync
            const expenseTransferAmountInput = document.getElementById('expenseTransferAmount');
            if (expenseTransferAmountInput) {
                const ewtSourceAmount = document.getElementById('ewtSourceAmount');
                const ewtDestTransferAmount = document.getElementById('ewtDestTransferAmount');
                const ewtExpenseAmount = document.getElementById('ewtExpenseAmount');
                const ewtDestAsset = document.getElementById('ewtDestAsset');
                const ewtExpenseAssetId = document.getElementById('ewtExpenseAssetId');

                expenseTransferAmountInput.addEventListener('input', function() {
                    const amount = parseFloat(this.value) || 0;
                    if (ewtSourceAmount) ewtSourceAmount.value = -amount;
                    if (ewtDestTransferAmount) ewtDestTransferAmount.value = amount;
                    if (ewtExpenseAmount) ewtExpenseAmount.value = -amount;
                });

                if (ewtDestAsset) {
                    ewtDestAsset.addEventListener('change', function() {
                        if (ewtExpenseAssetId) {
                            ewtExpenseAssetId.value = this.value;
                        }
                    });
                }
            }
        });
    </script>
@endpush
