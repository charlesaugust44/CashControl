@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/event-detail.css'])
@endpush

@push('scripts')
    @vite(['resources/js/delete-modal.js'])
@endpush

@section('content')
    @php
        $type = $event->type?->value ?? 'event';
        $typeIcon = $event->type?->icon() ?? 'bi-tag';
        $isConsolidated = $event->consolidated ?? false;
        $isTransferConsolidated = $event->transfer_consolidated ?? false;
        $isTransfer = $event->isTransfer();
        $isExpenseWithTransfer = $event->isExpenseWithTransfer();
        $isIncomeWithTransfer = $event->isIncomeWithTransfer();
        $isComposite = $event->isComposite();
        $isPartiallyConsolidated = $event->isPartiallyConsolidated();
        $isFullyConsolidated = $event->isFullyConsolidated();
        $isExpenseIncomeConsolidated = $isComposite && $isConsolidated;
        $isTransferPartConsolidated = $isComposite && $isTransferConsolidated;

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
                    @elseif($isFullyConsolidated)
                        <span class="badge bg-success">Consolidated</span>
                    @elseif($isPartiallyConsolidated)
                        <span class="badge bg-info">{{ __('entries.status.partial') }}</span>
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

                @if(!$isFullyConsolidated)
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
                                $sourceEntry = $event->getSourceEntry();
                                $destEntry = $event->getDestEntry();
                                $transferAmount = $event->getTransferAmount();
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
                                $sourceEntry = $event->getSourceEntry();
                                $destTransferEntry = $event->getDestEntry();
                                $amount = $event->getTransferAmount();
                                $transferDisabled = $isExpenseIncomeConsolidated && !$isTransferPartConsolidated ? false : ($isTransferPartConsolidated || $isFullyConsolidated);
                                $expenseDisabled = $isExpenseIncomeConsolidated || $isFullyConsolidated;
                                $amountDisabled = $isExpenseIncomeConsolidated || $isTransferPartConsolidated || $isFullyConsolidated;
                            @endphp

                            <input type="hidden" name="positions[0]" value="0">
                            <input type="hidden" name="positions[1]" value="1">
                            <input type="hidden" name="positions[2]" value="2">

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
                                            {{ $amountDisabled ? 'disabled' : 'required' }}
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="transfer-assets-row">
                                <div class="form-group">
                                    <label class="form-label">{{ __('entries.fields.from_source') }}</label>
                                    <select name="entries[0][asset_id]" id="ewtSourceAsset" class="form-control" {{ $transferDisabled ? 'disabled' : 'required' }}>
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
                                    <select name="entries[1][asset_id]" id="ewtDestAsset" class="form-control" {{ $transferDisabled ? 'disabled' : 'required' }}>
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
                                $incomeEntry = $event->getIncomeEntry();
                                $sourceTransferEntry = $event->getSourceEntry();
                                $destTransferEntry = $event->getLastPositiveEntry();
                                $amount = abs($incomeEntry?->amount ?? 0);
                                $incomeDisabled = $isExpenseIncomeConsolidated || $isFullyConsolidated;
                                $transferDisabled = $isTransferPartConsolidated || $isFullyConsolidated;
                                $amountDisabled = $isExpenseIncomeConsolidated || $isTransferPartConsolidated || $isFullyConsolidated;
                            @endphp

                            <input type="hidden" name="positions[0]" value="0">
                            <input type="hidden" name="positions[1]" value="1">
                            <input type="hidden" name="positions[2]" value="2">

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
                                            {{ $amountDisabled ? 'disabled' : 'required' }}
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="transfer-assets-row">
                                <div class="form-group">
                                    <label class="form-label">{{ __('entries.fields.from_source') }} / {{ __('entries.fields.asset') }}</label>
                                    <select name="entries[0][asset_id]" id="iwtSourceAsset" class="form-control" {{ $incomeDisabled ? 'disabled' : 'required' }}>
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
                                    <select name="entries[2][asset_id]" id="iwtDestAsset" class="form-control" {{ $transferDisabled ? 'disabled' : 'required' }}>
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
                        {{ $isFullyConsolidated ? 'disabled' : '' }}
                    >{{ old('note', $event->note ?? '') }}</textarea>
                </div>

                <div class="form-actions">
                    @if(!$isVirtual && !$isFullyConsolidated)
                        <div class="form-actions__danger">
                            <button type="button" class="btn btn-danger btn-icon" title="{{ __('ui.delete') }}" data-delete-modal-trigger>
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    @endif

                    <div class="form-actions__group">
                        @if(!$isVirtual && ($isPartiallyConsolidated || $isFullyConsolidated))
                            <button type="submit" name="action" value="unconsolidate" class="btn btn-warning">
                                <i class="bi bi-arrow-counterclockwise"></i> {{ __('entries.actions.revert') }}
                            </button>
                        @endif

                        @if(!$isFullyConsolidated)
                            @if($isComposite && !$isExpenseIncomeConsolidated && !$isTransferPartConsolidated)
                                <button type="submit" name="action" value="consolidate_expense_income" class="btn btn-success">
                                    <i class="bi bi-check-all"></i> {{ __('entries.actions.' . ($isExpenseWithTransfer ? 'paid' : 'received')) }}
                                </button>
                                <button type="submit" name="action" value="consolidate_transfer" class="btn btn-success">
                                    <i class="bi bi-check-all"></i> {{ __('entries.actions.transferred') }}
                                </button>
                            @elseif($isComposite && $isExpenseIncomeConsolidated && !$isTransferPartConsolidated)
                                <button type="submit" name="action" value="consolidate_transfer" class="btn btn-success">
                                    <i class="bi bi-check-all"></i> {{ __('entries.actions.transferred') }}
                                </button>
                            @elseif($isComposite && !$isExpenseIncomeConsolidated && $isTransferPartConsolidated)
                                <button type="submit" name="action" value="consolidate_expense_income" class="btn btn-success">
                                    <i class="bi bi-check-all"></i> {{ __('entries.actions.' . ($isExpenseWithTransfer ? 'paid' : 'received')) }}
                                </button>
                            @elseif(!$isComposite && !$isConsolidated)
                                @php
                                    $consolidateLabel = match($type) {
                                        'expense' => 'paid',
                                        'income' => 'received',
                                        'transfer' => 'transferred',
                                        default => 'paid',
                                    };
                                @endphp
                                <button type="submit" name="action" value="consolidate" class="btn btn-success">
                                    <i class="bi bi-check-all"></i> {{ __('entries.actions.' . $consolidateLabel) }}
                                </button>
                            @endif

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

    @if(!$isVirtual && !$isFullyConsolidated)
        <x-delete-modal
            :action="url('/entries/' . $event->id)"
            :title="__('entries.delete_confirmation.title')"
            :message="__('entries.delete_confirmation.message', ['name' => e($event->name)])"
        />
    @endif

    @if(!$isFullyConsolidated)
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
        const entryStructures = @json($entryStructures);
        const eventType = @json($event->type?->value ?? 'event');

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

        function syncEntryAmounts(type, amount) {
            const structure = entryStructures[type];
            if (!structure) return;

            structure.forEach((entry, i) => {
                const input = document.querySelector(`input[name="entries[${i}][amount]"]`);
                if (input && input.type === 'hidden') {
                    input.value = entry.sign * amount;
                }
            });
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

            const amountInputIds = {
                'transfer': 'transferAmount',
                'expense_with_transfer': 'expenseTransferAmount',
                'income_with_transfer': 'incomeTransferAmount',
            };

            const amountInputId = amountInputIds[eventType];
            if (amountInputId) {
                const amountInput = document.getElementById(amountInputId);
                if (amountInput) {
                    amountInput.addEventListener('input', function() {
                        syncEntryAmounts(eventType, parseFloat(this.value) || 0);
                    });
                }
            }

            const ewtDestAsset = document.getElementById('ewtDestAsset');
            const ewtExpenseAssetId = document.getElementById('ewtExpenseAssetId');
            if (ewtDestAsset && ewtExpenseAssetId) {
                ewtDestAsset.addEventListener('change', function() {
                    ewtExpenseAssetId.value = this.value;
                });
            }

            const iwtSourceAsset = document.getElementById('iwtSourceAsset');
            const iwtTransferAssetId = document.getElementById('iwtTransferAssetId');
            if (iwtSourceAsset && iwtTransferAssetId) {
                iwtSourceAsset.addEventListener('change', function() {
                    iwtTransferAssetId.value = this.value;
                });
            }
        });
    </script>
@endpush
