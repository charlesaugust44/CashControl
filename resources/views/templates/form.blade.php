@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/templates.css'])
@endpush

@section('content')
    <div class="template-form-wrapper">
        <div class="template-form-card">
            <h2 class="template-form-card__title">{{ isset($header) ? __('templates.edit') : __('templates.new') }}</h2>

            <form method="POST" action="{{ isset($header) ? url("/templates/{$header->id}") : url('/templates') }}" class="template-form" id="templateForm">
                @csrf
                @if(isset($header))
                    @method('PUT')
                @endif

                <div class="form-section">
                    <h3 class="form-section__title">{{ __('templates.sections.basic_info') }}</h3>

                    <div class="mb-4">
                        <label for="name" class="form-label">{{ __('templates.fields.name') }}</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $header->name ?? '') }}" placeholder="{{ __('templates.placeholders.name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">{{ __('templates.fields.description_optional') }}</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2" placeholder="{{ __('templates.placeholders.description') }}">{{ old('description', $header->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label for="type" class="form-label">{{ __('templates.fields.type') }}</label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">{{ __('ui.select') }} {{ __('templates.fields.type') }}</option>
                                <option value="income" {{ old('type', $header->type->value ?? '') === 'income' ? 'selected' : '' }}>{{ __('templates.types.income') }}</option>
                                <option value="expense" {{ old('type', $header->type->value ?? '') === 'expense' ? 'selected' : '' }}>{{ __('templates.types.expense') }}</option>
                                <option value="transfer" {{ old('type', $header->type->value ?? '') === 'transfer' ? 'selected' : '' }}>{{ __('templates.types.transfer') }}</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-col">
                            <label for="rule" class="form-label">{{ __('templates.fields.rule') }}</label>
                            <select class="form-control @error('rule') is-invalid @enderror" id="rule" name="rule" required>
                                <option value="">{{ __('ui.select') }} {{ __('templates.fields.rule') }}</option>
                                <option value="fixed" {{ old('rule', $header->rule->value ?? '') === 'fixed' ? 'selected' : '' }}>{{ __('templates.rules.fixed') }}</option>
                                <option value="max_last_five_months" {{ old('rule', $header->rule->value ?? '') === 'max_last_five_months' ? 'selected' : '' }}>{{ __('templates.rules.max_last_five_months') }}</option>
                                <option value="mean_last_five_months" {{ old('rule', $header->rule->value ?? '') === 'mean_last_five_months' ? 'selected' : '' }}>{{ __('templates.rules.mean_last_five_months') }}</option>
                            </select>
                            @error('rule')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4" id="defaultAmountField">
                        <label for="default_amount" class="form-label">{{ __('templates.fields.default_amount') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $fmt->currencySymbol() }}</span>
                            <input type="number" step="0.01" min="0" class="form-control @error('default_amount') is-invalid @enderror" id="default_amount" name="default_amount" value="{{ old('default_amount', $header->default_amount ?? '') }}" placeholder="{{ __('templates.placeholders.default_amount') }}">
                        </div>
                        @error('default_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">{{ __('templates.help.default_amount') }}</small>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section__title">{{ __('templates.schedule.title') }}</h3>

                    <div class="form-row">
                        <div class="form-col">
                            <label for="start_date" class="form-label">{{ __('templates.fields.start_date') }}</label>
                            <input type="month" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', isset($header) ? $header->start_date->format('Y-m') : now()->format('Y-m')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-col">
                            <label for="end_date" class="form-label">{{ __('templates.fields.end_date_optional') }}</label>
                            <input type="month" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', isset($header) && $header->end_date ? $header->end_date->format('Y-m') : '') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text">{{ __('templates.help.end_date') }}</small>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section__title">{{ __('ui.assets') }}</h3>

                    <div class="mb-4">
                        <label for="asset_id" class="form-label" id="assetLabel">{{ __('templates.fields.asset') }}</label>
                        <select class="form-control @error('asset_id') is-invalid @enderror" id="asset_id" name="asset_id" required>
                            <option value="">{{ __('templates.fields.asset') }}</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ old('asset_id', $header->asset_id ?? '') == $asset->id ? 'selected' : '' }}>{{ $asset->name }}</option>
                            @endforeach
                        </select>
                        @error('asset_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4" id="destinationAssetField" style="display: none;">
                        <label for="destination_asset_id" class="form-label">{{ __('templates.fields.destination_asset') }}</label>
                        <select class="form-control @error('destination_asset_id') is-invalid @enderror" id="destination_asset_id" name="destination_asset_id">
                            <option value="">{{ __('templates.fields.destination_asset') }}</option>
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
                        <h3 class="form-section__title">{{ __('templates.affected_events.title') }}</h3>
                        <p class="conflict-section__description">{{ __('templates.affected_events.edit_description') }}</p>

                        <div class="conflict-events-list">
                            @foreach($futureEvents as $event)
                                <div class="conflict-event">
                                    <div class="conflict-event__info">
                                        <span class="conflict-event__date">{{ $event->date->format('M Y') }}</span>
                                        @foreach($event->entries as $entry)
                                            <span class="conflict-event__entry">
                                                <i class="bi bi-wallet2"></i>
                                                {{ $entry->asset->name ?? __('ui.none') }}:
                                                <span class="{{ $entry->amount >= 0 ? 'positive' : 'negative' }}">
                                                    {{ $fmt->currency(abs($entry->amount)) }}
                                                </span>
                                            </span>
                                        @endforeach
                                    </div>
                                    <div class="conflict-event__actions">
                                        <label class="conflict-toggle">
                                            <input type="checkbox" name="delete_events[]" value="{{ $event->id }}" {{ in_array($event->id, old('delete_events', [])) ? 'checked' : '' }}>
                                            <span class="conflict-toggle__label">{{ __('templates.affected_events.delete') }}</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="template-form__actions">
                    <a href="{{ isset($header) ? url('/templates/' . $header->id) : url('/templates') }}" class="btn btn-outline-secondary">{{ __('ui.cancel') }}</a>
                    <button type="submit" name="action" value="save" class="btn btn-primary">
                        <i class="bi bi-save"></i> {{ __('ui.save') }}
                    </button>
                    <button type="submit" name="action" value="submit" class="btn btn-secondary">
                        <i class="bi bi-check-circle"></i> {{ __('ui.submit') }}
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

            const translations = {
                asset: @json(__('templates.fields.asset')),
                sourceAsset: @json(__('templates.fields.source_asset')),
            };

            function updateTypeFields() {
                const isTransfer = typeSelect.value === 'transfer';
                destinationField.style.display = isTransfer ? 'block' : 'none';
                assetLabel.textContent = isTransfer ? translations.sourceAsset : translations.asset;
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
