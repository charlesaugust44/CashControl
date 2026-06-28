@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/templates.css'])
@endpush

@section('content')
    <div class="template-form-wrapper">
        <div class="template-form-card">
            <h2 class="template-form-card__title">{{ isset($header) ? 'Edit Template' : 'New Template' }}</h2>

            <form method="POST" action="{{ isset($header) ? url("/templates/{$header->id}") : url('/templates') }}" class="template-form" id="templateForm">
                @csrf
                @if(isset($header))
                    @method('PUT')
                @endif

                <div class="form-section">
                    <h3 class="form-section__title">Basic Information</h3>

                    <div class="mb-4">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $header->name ?? '') }}" placeholder="e.g., Monthly Salary" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Description (optional)</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2" placeholder="Brief description of this template">{{ old('description', $header->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Select type</option>
                                <option value="income" {{ old('type', $header->type->value ?? '') === 'income' ? 'selected' : '' }}>Income</option>
                                <option value="expense" {{ old('type', $header->type->value ?? '') === 'expense' ? 'selected' : '' }}>Expense</option>
                                <option value="transfer" {{ old('type', $header->type->value ?? '') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-col">
                            <label for="rule" class="form-label">Rule</label>
                            <select class="form-control @error('rule') is-invalid @enderror" id="rule" name="rule" required>
                                <option value="">Select rule</option>
                                <option value="fixed" {{ old('rule', $header->rule->value ?? '') === 'fixed' ? 'selected' : '' }}>Fixed</option>
                                <option value="max_last_five_months" {{ old('rule', $header->rule->value ?? '') === 'max_last_five_months' ? 'selected' : '' }}>Max of last 5 months</option>
                                <option value="mean_last_five_months" {{ old('rule', $header->rule->value ?? '') === 'mean_last_five_months' ? 'selected' : '' }}>Mean of last 5 months</option>
                            </select>
                            @error('rule')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4" id="defaultAmountField">
                        <label for="default_amount" class="form-label">Default Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" class="form-control @error('default_amount') is-invalid @enderror" id="default_amount" name="default_amount" value="{{ old('default_amount', $header->default_amount ?? '') }}" placeholder="0.00">
                        </div>
                        @error('default_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">Used for fixed rule. For max/mean rules, this is the fallback when no history exists.</small>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section__title">Schedule</h3>

                    <div class="form-row">
                        <div class="form-col">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="month" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', isset($header) ? $header->start_date->format('Y-m') : now()->format('Y-m')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-col">
                            <label for="end_date" class="form-label">End Date (optional)</label>
                            <input type="month" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', isset($header) && $header->end_date ? $header->end_date->format('Y-m') : '') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text">Leave empty for ongoing templates.</small>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section__title">Assets</h3>

                    <div class="mb-4">
                        <label for="asset_id" class="form-label" id="assetLabel">Asset</label>
                        <select class="form-control @error('asset_id') is-invalid @enderror" id="asset_id" name="asset_id" required>
                            <option value="">Select asset</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ old('asset_id', $header->asset_id ?? '') == $asset->id ? 'selected' : '' }}>{{ $asset->name }}</option>
                            @endforeach
                        </select>
                        @error('asset_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4" id="destinationAssetField" style="display: none;">
                        <label for="destination_asset_id" class="form-label">Destination Asset</label>
                        <select class="form-control @error('destination_asset_id') is-invalid @enderror" id="destination_asset_id" name="destination_asset_id">
                            <option value="">Select destination asset</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ old('destination_asset_id', $header->destination_asset_id ?? '') == $asset->id ? 'selected' : '' }}>{{ $asset->name }}</option>
                            @endforeach
                        </select>
                        @error('destination_asset_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @if(isset($futureEvents) && $futureEvents->isNotEmpty())
                    <div class="form-section conflict-section">
                        <h3 class="form-section__title">Affected Future Events</h3>
                        <p class="conflict-section__description">These events have been saved with custom values. Choose whether to keep them as-is or delete them (they will revert to forecast using the updated template).</p>

                        <div class="conflict-events-list">
                            @foreach($futureEvents as $event)
                                <div class="conflict-event">
                                    <div class="conflict-event__info">
                                        <span class="conflict-event__date">{{ $event->date->format('M Y') }}</span>
                                        @foreach($event->entries as $entry)
                                            <span class="conflict-event__entry">
                                                <i class="bi bi-wallet2"></i>
                                                {{ $entry->asset->name ?? 'Unknown' }}:
                                                <span class="{{ $entry->amount >= 0 ? 'positive' : 'negative' }}">
                                                    {{ number_format(abs($entry->amount), 2) }}
                                                </span>
                                            </span>
                                        @endforeach
                                    </div>
                                    <div class="conflict-event__actions">
                                        <label class="conflict-toggle">
                                            <input type="checkbox" name="delete_events[]" value="{{ $event->id }}" {{ in_array($event->id, old('delete_events', [])) ? 'checked' : '' }}>
                                            <span class="conflict-toggle__label">Delete</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="template-form__actions">
                    <a href="{{ isset($header) ? url('/templates/' . $header->id) : url('/templates') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" name="action" value="save" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save
                    </button>
                    <button type="submit" name="action" value="submit" class="btn btn-secondary">
                        <i class="bi bi-check-circle"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const destinationField = document.getElementById('destinationAssetField');
            const assetLabel = document.getElementById('assetLabel');
            const ruleSelect = document.getElementById('rule');
            const defaultAmountField = document.getElementById('defaultAmountField');

            function updateTypeFields() {
                const isTransfer = typeSelect.value === 'transfer';
                destinationField.style.display = isTransfer ? 'block' : 'none';
                assetLabel.textContent = isTransfer ? 'Source Asset' : 'Asset';
                if (!isTransfer) {
                    document.getElementById('destination_asset_id').value = '';
                }
            }

            function updateRuleFields() {
                const isFixed = ruleSelect.value === 'fixed';
                const amountInput = document.getElementById('default_amount');
                if (isFixed) {
                    amountInput.setAttribute('required', 'required');
                } else {
                    amountInput.removeAttribute('required');
                }
            }

            typeSelect.addEventListener('change', updateTypeFields);
            ruleSelect.addEventListener('change', updateRuleFields);

            updateTypeFields();
            updateRuleFields();
        });
    </script>
@endsection
