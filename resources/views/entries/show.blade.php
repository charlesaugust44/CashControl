@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/event-detail.css'])
@endpush

@section('content')
    @php
        $type = $event->type?->value ?? 'event';
        $typeIcons = ['income' => 'bi-arrow-down-left', 'expense' => 'bi-arrow-up-right', 'transfer' => 'bi-arrow-left-right', 'expense_with_transfer' => 'bi-cart-plus', 'income_with_transfer' => 'bi-cash-coin'];
        $typeIcon = $typeIcons[$type] ?? 'bi-tag';
        $isConsolidated = $event->consolidated ?? false;
        $isTransfer = $event->isTransfer();
        $isExpenseWithTransfer = $event->isExpenseWithTransfer();
        $isIncomeWithTransfer = $event->isIncomeWithTransfer();

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
                {{ $fmt->month($event->date) }}
            </div>

            <form method="POST" action="{{ $formAction }}" class="event-detail-form" id="eventForm">
                @csrf
                @if(!$isVirtual)
                    @method($formMethod)
                @endif

                @if(!$isConsolidated)
                    @php
                        $eventMonth = \Carbon\Carbon::parse($event->date);
                        $daysInMonth = $eventMonth->daysInMonth;
                    @endphp
                    <div class="event-meta-section">
                        <div class="form-group">
                            <label for="due_day" class="form-label">{{ __('entries.fields.due_day') }}</label>
                            <select name="due_day" id="dueDay" class="form-control">
                                <option value="">{{ __('entries.fields.no_due_day') }}</option>
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    <option value="{{ $d }}" {{ old('due_day', $event->due_day) == $d ? 'selected' : '' }}>{{ $d }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                @endif

                <div class="event-entries-section">
                    <h3 class="section-title">{{ __('entries.entries_section.title') }}</h3>

                    <div class="entries-list" id="entriesList">
                        @if($isTransfer)
                            @php
                                $sourceEntry = $event->entries->first(fn($e) => $e->amount < 0);
                                $destEntry = $event->entries->first(fn($e) => $e->amount > 0);
                                $transferAmount = abs($sourceEntry->amount ?? 0);
                            @endphp
                            
                            <div class="transfer-amount-section">
                                <div class="form-group">
                                    <label class="form-label">{{ __('entries.fields.transfer_amount') }}</label>
                                    <div class="amount-input-wrapper">
                                        <span class="currency-symbol">{{ $fmt->currencySymbol() }}</span>
                                        <input
                                            type="text"
                                            inputmode="decimal"
                                            autocomplete="off"
                                            name="transfer_amount"
                                            id="transferAmount"
                                            class="form-control money-input"
                                            value="{{ $transferAmount }}"
                                            {{ $isConsolidated ? 'disabled' : 'required' }}
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="transfer-assets-row">
                                <div class="form-group">
                                    <label class="form-label">{{ __('entries.fields.from') }}</label>
                                    <select name="entries[0][asset_id]" class="form-control" {{ $isConsolidated ? 'disabled' : 'required' }}>
                                        <option value="">{{ __('entries.select_source_asset') }}</option>
                                        @foreach($assets as $asset)
                                            <option value="{{ $asset->id }}" {{ $sourceEntry && $sourceEntry->asset_id == $asset->id ? 'selected' : '' }}>
                                                {{ $asset->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="entries[0][amount]" value="-{{ $transferAmount }}">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">{{ __('entries.fields.to') }}</label>
                                    <select name="entries[1][asset_id]" class="form-control" {{ $isConsolidated ? 'disabled' : 'required' }}>
                                        <option value="">{{ __('entries.select_destination_asset') }}</option>
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
                                    <label class="form-label">{{ __('entries.fields.amount') }}</label>
                                    <div class="amount-input-wrapper">
                                        <span class="currency-symbol">{{ $fmt->currencySymbol() }}</span>
                                        <input
                                            type="text"
                                            inputmode="decimal"
                                            autocomplete="off"
                                            name="expense_transfer_amount"
                                            id="expenseTransferAmount"
                                            class="form-control money-input"
                                            value="{{ $amount }}"
                                            {{ $isConsolidated ? 'disabled' : 'required' }}
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="transfer-assets-row">
                                <div class="form-group">
                                    <label class="form-label">{{ __('entries.fields.from_source') }}</label>
                                    <select name="entries[0][asset_id]" id="ewtSourceAsset" class="form-control" {{ $isConsolidated ? 'disabled' : 'required' }}>
                                        <option value="">{{ __('entries.select_source_asset') }}</option>
                                        @foreach($assets as $asset)
                                            <option value="{{ $asset->id }}" {{ $sourceEntry && $sourceEntry->asset_id == $asset->id ? 'selected' : '' }}>
                                                {{ $asset->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="entries[0][amount]" id="ewtSourceAmount" value="-{{ $amount }}">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">{{ __('entries.fields.to_expense_asset') }}</label>
                                    <select name="entries[1][asset_id]" id="ewtDestAsset" class="form-control" {{ $isConsolidated ? 'disabled' : 'required' }}>
                                        <option value="">{{ __('entries.select_destination_asset') }}</option>
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
                        @elseif($isIncomeWithTransfer)
                            @php
                                $incomeEntry = $event->entries->first(fn($e) => $e->amount > 0 && $e->asset_id === $event->header?->asset_id);
                                $sourceTransferEntry = $event->entries->first(fn($e) => $e->amount < 0);
                                $destTransferEntry = $event->entries->last(fn($e) => $e->amount > 0);
                                $amount = abs($incomeEntry->amount ?? 0);
                            @endphp

                            <div class="transfer-amount-section">
                                <div class="form-group">
                                    <label class="form-label">{{ __('entries.fields.amount') }}</label>
                                    <div class="amount-input-wrapper">
                                        <span class="currency-symbol">{{ $fmt->currencySymbol() }}</span>
                                        <input
                                            type="text"
                                            inputmode="decimal"
                                            autocomplete="off"
                                            name="income_transfer_amount"
                                            id="incomeTransferAmount"
                                            class="form-control money-input"
                                            value="{{ $amount }}"
                                            {{ $isConsolidated ? 'disabled' : 'required' }}
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="transfer-assets-row">
                                <div class="form-group">
                                    <label class="form-label">{{ __('entries.fields.from_source') }} / {{ __('entries.fields.asset') }}</label>
                                    <select name="entries[0][asset_id]" id="iwtSourceAsset" class="form-control" {{ $isConsolidated ? 'disabled' : 'required' }}>
                                        <option value="">{{ __('entries.select_source_asset') }}</option>
                                        @foreach($assets as $asset)
                                            <option value="{{ $asset->id }}" {{ $incomeEntry && $incomeEntry->asset_id == $asset->id ? 'selected' : '' }}>
                                                {{ $asset->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="entries[0][amount]" id="iwtIncomeAmount" value="{{ $amount }}">
                                    <input type="hidden" name="entries[1][asset_id]" id="iwtTransferAssetId" value="{{ $sourceTransferEntry->asset_id ?? '' }}">
                                    <input type="hidden" name="entries[1][amount]" id="iwtSourceAmount" value="-{{ $amount }}">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">{{ __('entries.fields.to') }}</label>
                                    <select name="entries[2][asset_id]" id="iwtDestAsset" class="form-control" {{ $isConsolidated ? 'disabled' : 'required' }}>
                                        <option value="">{{ __('entries.select_destination_asset') }}</option>
                                        @foreach($assets as $asset)
                                            <option value="{{ $asset->id }}" {{ $destTransferEntry && $destTransferEntry->asset_id == $asset->id ? 'selected' : '' }}>
                                                {{ $asset->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="entries[2][amount]" id="iwtDestTransferAmount" value="{{ $amount }}">
                                </div>
                            </div>
                        @else
                            @foreach($event->entries as $index => $entry)
                                @php
                                    $absoluteAmount = abs($entry->amount);
                                @endphp
                                <div class="entry-row" data-index="{{ $index }}">
                                    <div class="entry-row-header">
                                        <span class="entry-label">{{ __('entries.fields.asset') }}</span>
                                        @if(!$isVirtual && !$isConsolidated && count($event->entries) > 1)
                                            <button type="button" class="btn-remove-entry" onclick="removeEntry({{ $index }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="entry-fields">
                                        <div class="form-group">
                                            <select name="entries[{{ $index }}][asset_id]" class="form-control" {{ $isConsolidated ? 'disabled' : 'required' }}>
                                                <option value="">{{ __('entries.select_asset') }}</option>
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
                                                    type="text"
                                                    inputmode="decimal"
                                                    autocomplete="off"
                                                    name="entries[{{ $index }}][amount]"
                                                    class="form-control entry-amount-input money-input"
                                                    value="{{ $absoluteAmount }}"
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

                    @if(!$isConsolidated && !$isTransfer && !$isExpenseWithTransfer && !$isIncomeWithTransfer)
                        <button type="button" class="btn-add-entry" onclick="addEntry()">
                            <i class="bi bi-plus-circle"></i>
                            <span>{{ __('entries.add_entry') }}</span>
                        </button>
                    @endif
                </div>

                <div class="event-note-section">
                    <label for="note" class="form-label">{{ __('entries.fields.note_optional') }}</label>
                    <textarea
                        name="note"
                        id="note"
                        class="form-control"
                        rows="3"
                        placeholder="{{ __('entries.fields.note_placeholder') }}"
                        {{ $isConsolidated ? 'disabled' : '' }}
                    >{{ old('note', $event->note ?? '') }}</textarea>
                </div>

                <div class="form-actions">
                    @if(!$isVirtual && !$isConsolidated)
                        <div class="form-actions__danger">
                            <a href="{{ url('/entries/' . $event->id . '/delete') }}" class="btn btn-danger btn-icon" title="{{ __('ui.delete') }}">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    @endif

                    <div class="form-actions__group">
                        @if(!$isVirtual && $isConsolidated)
                            <button type="submit" name="action" value="unconsolidate" class="btn btn-warning">
                                <i class="bi bi-arrow-counterclockwise"></i> {{ __('entries.actions.unconsolidate') }}
                            </button>
                        @endif

                        @if(!$isConsolidated)
                            <button type="submit" name="action" value="consolidate" class="btn btn-success">
                                <i class="bi bi-check-all"></i> {{ __('entries.actions.consolidate') }}
                            </button>
                        @endif

                        @if(!$isConsolidated)
                            <div class="btn-split">
                                <button type="submit" name="action" value="save" class="btn btn-primary">
                                    <i class="bi bi-save"></i> {{ __('ui.save') }}
                                </button>
                                <button type="button" class="btn btn-primary btn-split__toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><button type="submit" name="action" value="submit" class="dropdown-item">
                                        <i class="bi bi-check-circle"></i> {{ __('ui.save_and_close') }}
                                    </button></li>
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(!$isConsolidated)
        <template id="entryTemplate">
            <div class="entry-row" data-index="__INDEX__">
                <div class="entry-row-header">
                    <span class="entry-label">{{ __('entries.fields.asset') }}</span>
                    <button type="button" class="btn-remove-entry" onclick="removeEntry(__INDEX__)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="entry-fields">
                    <div class="form-group">
                        <select name="entries[__INDEX__][asset_id]" class="form-control" required>
                            <option value="">{{ __('entries.select_asset') }}</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="amount-input-wrapper">
                            <span class="currency-symbol">{{ $fmt->currencySymbol() }}</span>
                            <input
                                type="text"
                                inputmode="decimal"
                                autocomplete="off"
                                name="entries[__INDEX__][amount]"
                                class="form-control entry-amount-input money-input"
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

            const incomeTransferAmountInput = document.getElementById('incomeTransferAmount');
            if (incomeTransferAmountInput) {
                const iwtIncomeAmount = document.getElementById('iwtIncomeAmount');
                const iwtSourceAmount = document.getElementById('iwtSourceAmount');
                const iwtDestTransferAmount = document.getElementById('iwtDestTransferAmount');

                incomeTransferAmountInput.addEventListener('input', function() {
                    const amount = parseFloat(this.value) || 0;
                    if (iwtIncomeAmount) iwtIncomeAmount.value = amount;
                    if (iwtSourceAmount) iwtSourceAmount.value = -amount;
                    if (iwtDestTransferAmount) iwtDestTransferAmount.value = amount;
                });

                const iwtSourceAsset = document.getElementById('iwtSourceAsset');
                const iwtTransferAssetId = document.getElementById('iwtTransferAssetId');
                if (iwtSourceAsset && iwtTransferAssetId) {
                    iwtSourceAsset.addEventListener('change', function() {
                        iwtTransferAssetId.value = this.value;
                    });
                }
            }
        });
    </script>
@endpush
