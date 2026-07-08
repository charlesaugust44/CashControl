@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/event-detail.css'])
@endpush

@section('content')
    <div class="event-detail-wrapper">
        <div class="event-detail-card">
            <div class="event-detail-header">
                <div class="event-detail-title">
                    <i class="bi bi-plus-circle"></i>
                    <h2>{{ __('entries.create') }}</h2>
                </div>
            </div>

            <form method="POST" action="{{ url('/entries') }}" class="event-detail-form" id="eventForm">
                @csrf

                <div class="event-meta-section">
                    <div class="form-group">
                        <label for="name" class="form-label">{{ __('entries.fields.name') }}</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control"
                            value="{{ old('name') }}"
                            maxlength="100"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('entries.fields.type') }}</label>
                        <x-type-selector name="type" value="{{ old('type') }}" />
                    </div>

                    <div class="form-group">
                        <label for="date" class="form-label">{{ __('entries.fields.date') }}</label>
                        <input
                            type="month"
                            name="date_month"
                            id="dateMonth"
                            class="form-control"
                            value="{{ old('date_month', $defaultDate->format('Y-m')) }}"
                            required
                        >
                        <input type="hidden" name="date" id="dateValue" value="{{ old('date', $defaultDate->format('Y-m-d')) }}">
                    </div>

                    <div class="form-group">
                        <label for="due_day" class="form-label">{{ __('entries.fields.due_day') }}</label>
                        <select name="due_day" id="dueDay" class="form-control">
                            <option value="">{{ __('entries.fields.no_due_day') }}</option>
                        </select>
                    </div>
                </div>

                <div class="event-entries-section">
                    <h3 class="section-title">{{ __('entries.singular') }}</h3>

                    <div id="normalEntries" class="entries-list">
                        <div class="entry-row" data-index="0">
                            <div class="entry-fields">
                                <div class="form-group">
                                    <label class="form-label">{{ __('entries.fields.asset') }}</label>
                                    <select name="entries[0][asset_id]" class="form-control" required>
                                        <option value="">{{ __('entries.select_asset') }}</option>
                                        @foreach($assets as $asset)
                                            <option value="{{ $asset->id }}" {{ old('entries.0.asset_id') == $asset->id ? 'selected' : '' }}>
                                                {{ $asset->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('entries.fields.amount') }}</label>
                                    <div class="amount-input-wrapper">
                                        <span class="currency-symbol">{{ $fmt->currencySymbol() }}</span>
                                        <input
                                            type="text"
                                            inputmode="decimal"
                                            autocomplete="off"
                                            name="entries[0][amount]"
                                            class="form-control money-input"
                                            value="{{ old('entries.0.amount') }}"
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="transferEntries" class="entries-list" style="display: none;">
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
                                        value="{{ old('transfer_amount') }}"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="transfer-assets-row">
                            <div class="form-group">
                                <label class="form-label">{{ __('entries.fields.from') }}</label>
                                <select name="entries[0][asset_id]" class="form-control" required>
                                    <option value="">{{ __('entries.select_source_asset') }}</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}" {{ old('entries.0.asset_id') == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="entries[0][amount]" id="sourceAmount" value="0">
                            </div>

                            <div class="form-group">
                                <label class="form-label">{{ __('entries.fields.to') }}</label>
                                <select name="entries[1][asset_id]" class="form-control" required>
                                    <option value="">{{ __('entries.select_destination_asset') }}</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}" {{ old('entries.1.asset_id') == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="entries[1][amount]" id="destAmount" value="0">
                            </div>
                        </div>
                    </div>

                    <div id="expenseWithTransferEntries" class="entries-list" style="display: none;">
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
                                        value="{{ old('expense_transfer_amount') }}"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="transfer-assets-row">
                            <div class="form-group">
                                <label class="form-label">{{ __('entries.fields.from') }}</label>
                                <select name="ewt_entries[0][asset_id]" id="ewtSourceAsset" class="form-control" required>
                                    <option value="">{{ __('entries.select_source_asset') }}</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}" {{ old('ewt_entries.0.asset_id') == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="ewt_entries[0][amount]" id="ewtSourceAmount" value="0">
                            </div>

                            <div class="form-group">
                                <label class="form-label">{{ __('entries.fields.to') }} / {{ __('entries.fields.asset') }}</label>
                                <select name="ewt_entries[1][asset_id]" id="ewtDestAsset" class="form-control" required>
                                    <option value="">{{ __('entries.select_destination_asset') }}</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}" {{ old('ewt_entries.1.asset_id') == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="ewt_entries[1][amount]" id="ewtDestTransferAmount" value="0">
                                <input type="hidden" name="ewt_entries[2][amount]" id="ewtExpenseAmount" value="0">
                            </div>
                        </div>
                    </div>

                    <div id="incomeWithTransferEntries" class="entries-list" style="display: none;">
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
                                        value="{{ old('income_transfer_amount') }}"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="transfer-assets-row">
                            <div class="form-group">
                                <label class="form-label">{{ __('entries.fields.from') }} / {{ __('entries.fields.asset') }}</label>
                                <select name="iwt_entries[0][asset_id]" id="iwtSourceAsset" class="form-control" required>
                                    <option value="">{{ __('entries.select_source_asset') }}</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}" {{ old('iwt_entries.0.asset_id') == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="iwt_entries[0][amount]" id="iwtIncomeAmount" value="0">
                                <input type="hidden" name="iwt_entries[1][amount]" id="iwtSourceAmount" value="0">
                            </div>

                            <div class="form-group">
                                <label class="form-label">{{ __('entries.fields.to') }}</label>
                                <select name="iwt_entries[2][asset_id]" id="iwtDestAsset" class="form-control" required>
                                    <option value="">{{ __('entries.select_destination_asset') }}</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}" {{ old('iwt_entries.2.asset_id') == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="iwt_entries[2][amount]" id="iwtDestTransferAmount" value="0">
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-add-entry" id="addEntryBtn" onclick="addEntry()">
                        <i class="bi bi-plus-circle"></i>
                        <span>{{ __('entries.add_entry') }}</span>
                    </button>
                </div>

                <div class="event-note-section">
                    <label for="note" class="form-label">{{ __('entries.fields.note') }} ({{ __('ui.optional') }})</label>
                    <textarea
                        name="note"
                        id="note"
                        class="form-control"
                        rows="3"
                        placeholder="{{ __('entries.fields.note_placeholder') }}"
                    >{{ old('note') }}</textarea>
                </div>
            </form>

            <div class="form-actions">
                <div class="form-actions__group">
                    <div class="btn-split">
                        <button type="submit" name="action" value="save" form="eventForm" class="btn btn-primary">
                            <i class="bi bi-save"></i> {{ __('ui.save') }}
                        </button>
                        <button type="button" class="btn btn-primary btn-split__toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><button type="submit" name="action" value="submit" form="eventForm" class="dropdown-item">
                                <i class="bi bi-check-circle"></i> {{ __('ui.save_and_close') }}
                            </button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                            class="form-control money-input"
                            required
                        >
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        let entryIndex = 1;
        const typeInput = document.querySelector('[data-type-selector] [data-type-input]');
        const normalEntries = document.getElementById('normalEntries');
        const transferEntries = document.getElementById('transferEntries');
        const expenseWithTransferEntries = document.getElementById('expenseWithTransferEntries');
        const incomeWithTransferEntries = document.getElementById('incomeWithTransferEntries');
        const addEntryBtn = document.getElementById('addEntryBtn');
        const dateMonthInput = document.getElementById('dateMonth');
        const dateValueInput = document.getElementById('dateValue');
        const dueDaySelect = document.getElementById('dueDay');
        const entryStructures = @json($entryStructures);

        function toggleTypeUI() {
            const isTransfer = typeInput.value === 'transfer';
            const isExpenseWithTransfer = typeInput.value === 'expense_with_transfer';
            const isIncomeWithTransfer = typeInput.value === 'income_with_transfer';
            const isNormal = !isTransfer && !isExpenseWithTransfer && !isIncomeWithTransfer;

            normalEntries.style.display = isNormal ? '' : 'none';
            transferEntries.style.display = isTransfer ? '' : 'none';
            expenseWithTransferEntries.style.display = isExpenseWithTransfer ? '' : 'none';
            incomeWithTransferEntries.style.display = isIncomeWithTransfer ? '' : 'none';
            addEntryBtn.style.display = isNormal ? '' : 'none';

            normalEntries.querySelectorAll('input, select').forEach(el => {
                el.disabled = !isNormal;
            });
            transferEntries.querySelectorAll('input, select').forEach(el => {
                el.disabled = !isTransfer;
            });
            expenseWithTransferEntries.querySelectorAll('input, select').forEach(el => {
                el.disabled = !isExpenseWithTransfer;
            });
            incomeWithTransferEntries.querySelectorAll('input, select').forEach(el => {
                el.disabled = !isIncomeWithTransfer;
            });

            MoneyInput.init();
        }

        function updateDate() {
            if (dateMonthInput.value) {
                dateValueInput.value = dateMonthInput.value + '-01';
            }
            updateDueDayOptions();
        }

        function updateDueDayOptions() {
            const currentValue = dueDaySelect.value;
            const selectedMonth = dateMonthInput.value;
            if (!selectedMonth) return;

            const [year, month] = selectedMonth.split('-').map(Number);
            const daysInMonth = new Date(year, month, 0).getDate();

            dueDaySelect.innerHTML = '<option value="">{{ __('entries.fields.no_due_day') }}</option>';
            for (let d = 1; d <= 31; d++) {
                const option = document.createElement('option');
                option.value = d;
                option.textContent = d;
                if (d > daysInMonth) {
                    option.disabled = true;
                    option.textContent = d + ' *';
                }
                dueDaySelect.appendChild(option);
            }

            if (currentValue && parseInt(currentValue) <= daysInMonth) {
                dueDaySelect.value = currentValue;
            } else if (currentValue && parseInt(currentValue) > daysInMonth) {
                dueDaySelect.value = daysInMonth;
            }
        }

        function syncAmounts(type, amountInput) {
            const amount = parseFloat(amountInput.value) || 0;
            const structure = entryStructures[type];
            const section = type === 'transfer' ? transferEntries :
                           type === 'expense_with_transfer' ? expenseWithTransferEntries :
                           incomeWithTransferEntries;

            structure.forEach((entry, i) => {
                const hiddenInput = section.querySelector(`input[name*="entries[${i}][amount]"]`) ||
                                   section.querySelector(`input[id*="Amount"]`);
                if (hiddenInput && hiddenInput.type === 'hidden') {
                    hiddenInput.value = entry.sign * amount;
                }
            });
        }

        function addEntry() {
            const template = document.getElementById('entryTemplate');
            const html = template.innerHTML.replace(/__INDEX__/g, entryIndex);
            normalEntries.insertAdjacentHTML('beforeend', html);
            entryIndex++;
        }

        function removeEntry(index) {
            const row = normalEntries.querySelector(`.entry-row[data-index="${index}"]`);
            if (row) {
                row.remove();
            }
        }

        typeInput.addEventListener('change', toggleTypeUI);
        dateMonthInput.addEventListener('change', updateDate);

        document.querySelectorAll('.money-input').forEach(input => {
            input.addEventListener('input', function() {
                const type = typeInput.value;
                if (type === 'transfer' || type === 'expense_with_transfer' || type === 'income_with_transfer') {
                    syncAmounts(type, this);
                }
            });
        });

        document.getElementById('eventForm').addEventListener('submit', function(e) {
            const type = typeInput.value;

            if (type === 'expense_with_transfer') {
                const sourceAsset = document.getElementById('ewtSourceAsset').value;
                const destAsset = document.getElementById('ewtDestAsset').value;
                const structure = entryStructures[type];

                structure.forEach((entry, i) => {
                    const assetId = entry.slot === 'source' ? sourceAsset : destAsset;
                    let existingInput = this.querySelector(`input[name="entries[${i}][asset_id]"]`);
                    if (!existingInput) {
                        existingInput = document.createElement('input');
                        existingInput.type = 'hidden';
                        existingInput.name = `entries[${i}][asset_id]`;
                        this.appendChild(existingInput);
                    }
                    existingInput.value = assetId;

                    let amountInput = this.querySelector(`input[name="entries[${i}][amount]"]`);
                    if (!amountInput) {
                        amountInput = document.createElement('input');
                        amountInput.type = 'hidden';
                        amountInput.name = `entries[${i}][amount]`;
                        this.appendChild(amountInput);
                    }
                    const amount = parseFloat(document.getElementById('expenseTransferAmount').value) || 0;
                    amountInput.value = entry.sign * amount;
                });

                this.querySelectorAll('input[name^="ewt_entries"]').forEach(el => el.disabled = true);
            } else if (type === 'income_with_transfer') {
                const sourceAsset = document.getElementById('iwtSourceAsset').value;
                const destAsset = document.getElementById('iwtDestAsset').value;
                const structure = entryStructures[type];

                structure.forEach((entry, i) => {
                    const assetId = entry.slot === 'source' ? sourceAsset : destAsset;
                    let existingInput = this.querySelector(`input[name="entries[${i}][asset_id]"]`);
                    if (!existingInput) {
                        existingInput = document.createElement('input');
                        existingInput.type = 'hidden';
                        existingInput.name = `entries[${i}][asset_id]`;
                        this.appendChild(existingInput);
                    }
                    existingInput.value = assetId;

                    let amountInput = this.querySelector(`input[name="entries[${i}][amount]"]`);
                    if (!amountInput) {
                        amountInput = document.createElement('input');
                        amountInput.type = 'hidden';
                        amountInput.name = `entries[${i}][amount]`;
                        this.appendChild(amountInput);
                    }
                    const amount = parseFloat(document.getElementById('incomeTransferAmount').value) || 0;
                    amountInput.value = entry.sign * amount;
                });

                this.querySelectorAll('input[name^="iwt_entries"]').forEach(el => el.disabled = true);
            }
        });

        toggleTypeUI();
        updateDate();
        updateDueDayOptions();
    </script>
@endpush
