@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/templates.css'])
@endpush

@push('scripts')
    @vite(['resources/js/delete-modal.js'])
@endpush

@section('content')
    <div class="template-detail-wrapper">
        <div class="template-detail-card">
            <div class="template-detail-header">
                <div class="template-detail-title">
                    @php
                        $typeIcons = ['income' => 'bi-arrow-down-left', 'expense' => 'bi-arrow-up-right', 'transfer' => 'bi-arrow-left-right', 'expense_with_transfer' => 'bi-cart-plus', 'income_with_transfer' => 'bi-cash-coin'];
                        $typeIcon = $typeIcons[$header->type->value] ?? 'bi-tag';
                    @endphp
                    <i class="{{ $typeIcon }} type-{{ $header->type->value }}"></i>
                    <h2>{{ $header->name }}</h2>
                </div>
                <span class="template-type-badge type-{{ $header->type->value }}">{{ __('templates.types.' . $header->type->value) }}</span>
            </div>

            @if($header->description)
                <p class="template-detail-description">{{ $header->description }}</p>
            @endif

            <div class="template-detail-section">
                <h3 class="template-detail-section__title">{{ __('templates.configuration.title') }}</h3>
                <div class="template-detail-grid">
                    <div class="template-detail-item">
                        <span class="template-detail-item__label">{{ __('templates.fields.rule') }}</span>
                        <span class="template-detail-item__value">{{ __('templates.rules.' . $header->rule->value) }}</span>
                    </div>
                    @if($header->default_amount)
                        <div class="template-detail-item">
                            <span class="template-detail-item__label">{{ __('templates.fields.default_amount') }}</span>
                            <span class="template-detail-item__value">{{ $fmt->currency($header->default_amount) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="template-detail-section">
                <h3 class="template-detail-section__title">{{ __('templates.schedule.title') }}</h3>
                <div class="template-detail-grid">
                    <div class="template-detail-item">
                        <span class="template-detail-item__label">{{ __('templates.fields.start_date') }}</span>
                        <span class="template-detail-item__value">{{ $header->start_date->translatedFormat('M Y') }}</span>
                    </div>
                    <div class="template-detail-item">
                        <span class="template-detail-item__label">{{ __('templates.fields.end_date') }}</span>
                        <span class="template-detail-item__value">{{ $header->end_date ? $header->end_date->translatedFormat('M Y') : __('templates.schedule.ongoing') }}</span>
                    </div>
                    <div class="template-detail-item">
                        <span class="template-detail-item__label">{{ __('templates.fields.due_day') }}</span>
                        <span class="template-detail-item__value">{{ $header->due_day ?? __('ui.none') }}</span>
                    </div>
                </div>
            </div>

            <div class="template-detail-section">
                <h3 class="template-detail-section__title">{{ __('ui.assets') }}</h3>
                <div class="template-detail-grid">
                    <div class="template-detail-item">
                        <span class="template-detail-item__label">{{ ($header->isTransfer() || $header->isExpenseWithTransfer() || $header->isIncomeWithTransfer()) ? __('templates.fields.source_asset') : __('templates.fields.asset') }}</span>
                        <span class="template-detail-item__value">{{ $header->asset->name ?? __('ui.none') }}</span>
                    </div>
                    @if(($header->isTransfer() || $header->isExpenseWithTransfer() || $header->isIncomeWithTransfer()) && $header->destinationAsset)
                        <div class="template-detail-item">
                            <span class="template-detail-item__label">{{ __('templates.fields.destination_asset') }}</span>
                            <span class="template-detail-item__value">{{ $header->destinationAsset->name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="form-actions">
                <div class="form-actions__danger">
                    <button type="button" class="btn btn-danger btn-icon" title="{{ __('ui.delete') }}" data-delete-modal-trigger>
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="form-actions__group">
                    <a href="{{ url('/templates/' . $header->id . '/edit') }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> {{ __('ui.edit') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <x-delete-modal
        :action="url('/templates/' . $header->id)"
        :title="__('templates.delete_confirmation.title')"
        :message="__('templates.delete_confirmation.message', ['name' => e($header->name)])"
    >
        @if(isset($futureEvents) && $futureEvents->isNotEmpty())
            <div class="conflict-section">
                <h3 class="conflict-section__title">{{ __('templates.affected_events.title') }}</h3>
                <p class="conflict-section__description">{{ __('templates.affected_events.delete_description') }}</p>

                <div class="conflict-events-list">
                    @foreach($futureEvents as $event)
                        <div class="conflict-event">
                            <div class="conflict-event__info">
                                <span class="conflict-event__date">{{ $event->date->translatedFormat('M Y') }}</span>
                                @if($event->consolidated)
                                    <span class="badge bg-success">{{ __('entries.status.consolidated') }}</span>
                                @endif
                                @foreach($event->entries as $entry)
                                    <span class="conflict-event__entry">
                                        <i class="bi bi-wallet2"></i>
                                        {{ $entry->asset->name ?? __('ui.none') }}:
                                        <span class="amount-{{ $entry->amount >= 0 ? 'positive' : 'negative' }}">
                                            {{ $fmt->currency(abs($entry->amount)) }}
                                        </span>
                                    </span>
                                @endforeach
                            </div>
                            <div class="conflict-event__actions">
                                <label class="conflict-toggle">
                                    <input type="checkbox" name="delete_events[]" value="{{ $event->id }}" checked>
                                    <span class="conflict-toggle__label">{{ __('templates.affected_events.delete') }}</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-delete-modal>
@endsection
