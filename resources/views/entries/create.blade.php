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
                        <label for="type" class="form-label">{{ __('entries.fields.type') }}</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>{{ __('templates.types.income') }}</option>
                            <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>{{ __('templates.types.expense') }}</option>
                            <option value="transfer" {{ old('type') === 'transfer' ? 'selected' : '' }}>{{ __('templates.types.transfer') }}</option>
                            <option value="expense_with_transfer" {{ old('type') === 'expense_with_transfer' ? 'selected' : '' }}>{{ __('templates.types.expense_with_transfer') }}</option>
                            <option value="income_with_transfer" {{ old('type') === 'income_with_transfer' ? 'selected' : '' }}>{{ __('templates.types.income_with_transfer') }}</option>
                        </select>
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
        const typeSelect = document.getElementById('type');
        const normalEntries = document.getElementById('normalEntries');
        const transferEntries = document.getElementById('transferEntries');
        const expenseWithTransferEntries = document.getElementById('expenseWithTransferEntries');
        const incomeWithTransferEntries = document.getElementById('incomeWithTransferEntries');
        const addEntryBtn = document.getElementById('addEntryBtn');
        const dateMonthInput = document.getElementById('dateMonth');
        const dateValueInput = document.getElementById('dateValue');
        const dueDaySelect = document.getElementById('dueDay');
        const transferAmountInput = document.getElementById('transferAmount');
        const sourceAmountInput = document.getElementById('sourceAmount');
        const destAmountInput = document.getElementById('destAmount');
        const expenseTransferAmountInput = document.getElementById('expenseTransferAmount');
        const ewtSourceAmountInput = document.getElementById('ewtSourceAmount');
        const ewtDestTransferAmountInput = document.getElementById('ewtDestTransferAmount');
        const ewtExpenseAmountInput = document.getElementById('ewtExpenseAmount');
        const ewtSourceAsset = document.getElementById('ewtSourceAsset');
        const ewtDestAsset = document.getElementById('ewtDestAsset');
        const incomeTransferAmountInput = document.getElementById('incomeTransferAmount');
        const iwtIncomeAmountInput = document.getElementById('iwtIncomeAmount');
        const iwtSourceAmountInput = document.getElementById('iwtSourceAmount');
        const iwtDestTransferAmountInput = document.getElementById('iwtDestTransferAmount');
        const iwtSourceAsset = document.getElementById('iwtSourceAsset');
        const iwtDestAsset = document.getElementById('iwtDestAsset');

        function toggleTypeUI() {
            const isTransfer = typeSelect.value === 'transfer';
            const isExpenseWithTransfer = typeSelect.value === 'expense_with_transfer';
            const isIncomeWithTransfer = typeSelect.value === 'income_with_transfer';
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

        function updateTransferAmounts() {
            const amount = parseFloat(transferAmountInput.value) || 0;
            sourceAmountInput.value = -amount;
            destAmountInput.value = amount;
        }

        function updateExpenseWithTransferAmounts() {
            const amount = parseFloat(expenseTransferAmountInput.value) || 0;
            ewtSourceAmountInput.value = -amount;
            ewtDestTransferAmountInput.value = amount;
            ewtExpenseAmountInput.value = -amount;
        }

        function updateIncomeWithTransferAmounts() {
            const amount = parseFloat(incomeTransferAmountInput.value) || 0;
            iwtIncomeAmountInput.value = amount;
            iwtSourceAmountInput.value = -amount;
            iwtDestTransferAmountInput.value = amount;
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

        typeSelect.addEventListener('change', toggleTypeUI);
        dateMonthInput.addEventListener('change', updateDate);
        if (transferAmountInput) {
            transferAmountInput.addEventListener('input', updateTransferAmounts);
        }
        if (expenseTransferAmountInput) {
            expenseTransferAmountInput.addEventListener('input', updateExpenseWithTransferAmounts);
        }
        if (incomeTransferAmountInput) {
            incomeTransferAmountInput.addEventListener('input', updateIncomeWithTransferAmounts);
        }

        document.getElementById('eventForm').addEventListener('submit', function(e) {
            if (typeSelect.value === 'transfer') {
                updateTransferAmounts();
            } else if (typeSelect.value === 'expense_with_transfer') {
                updateExpenseWithTransferAmounts();
                const destAssetId = ewtDestAsset.value;
                for (let i = 0; i < 3; i++) {
                    const existingInput = document.querySelector(`input[name="entries[${i}][asset_id]"]`);
                    if (!existingInput) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `entries[${i}][asset_id]`;
                        input.value = i === 0 ? ewtSourceAsset.value : destAssetId;
                        this.appendChild(input);
                    }
                    const amountInput = document.createElement('input');
                    amountInput.type = 'hidden';
                    amountInput.name = `entries[${i}][amount]`;
                    amountInput.value = i === 0 ? ewtSourceAmountInput.value : (i === 1 ? ewtDestTransferAmountInput.value : ewtExpenseAmountInput.value);
                    this.appendChild(amountInput);
                }
                document.querySelectorAll('input[name^="ewt_entries"]').forEach(el => el.disabled = true);
            } else if (typeSelect.value === 'income_with_transfer') {
                updateIncomeWithTransferAmounts();
                const sourceAssetId = iwtSourceAsset.value;
                const destAssetId = iwtDestAsset.value;
                for (let i = 0; i < 3; i++) {
                    const existingInput = document.querySelector(`input[name="entries[${i}][asset_id]"]`);
                    if (!existingInput) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `entries[${i}][asset_id]`;
                        input.value = i < 2 ? sourceAssetId : destAssetId;
                        this.appendChild(input);
                    }
                    const amountInput = document.createElement('input');
                    amountInput.type = 'hidden';
                    amountInput.name = `entries[${i}][amount]`;
                    amountInput.value = i === 0 ? iwtIncomeAmountInput.value : (i === 1 ? iwtSourceAmountInput.value : iwtDestTransferAmountInput.value);
                    this.appendChild(amountInput);
                }
                document.querySelectorAll('input[name^="iwt_entries"]').forEach(el => el.disabled = true);
            }
        });

        toggleTypeUI();
        updateDate();
        updateDueDayOptions();
    </script>
@endpush
