@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/templates.css'])
@endpush

@section('content')
    <div class="template-detail-wrapper">
        <div class="template-detail-card">
            <div class="template-detail-header">
                <div class="template-detail-title">
                    @php
                        $typeIcons = ['income' => 'bi-arrow-down-left', 'expense' => 'bi-arrow-up-right', 'transfer' => 'bi-arrow-left-right'];
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

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
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
                            <span class="template-detail-item__value">${{ number_format($header->default_amount, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="template-detail-section">
                <h3 class="template-detail-section__title">{{ __('templates.schedule.title') }}</h3>
                <div class="template-detail-grid">
                    <div class="template-detail-item">
                        <span class="template-detail-item__label">{{ __('templates.fields.start_date') }}</span>
                        <span class="template-detail-item__value">{{ $header->start_date->format('M Y') }}</span>
                    </div>
                    <div class="template-detail-item">
                        <span class="template-detail-item__label">{{ __('templates.fields.end_date') }}</span>
                        <span class="template-detail-item__value">{{ $header->end_date ? $header->end_date->format('M Y') : __('templates.schedule.ongoing') }}</span>
                    </div>
                </div>
            </div>

            <div class="template-detail-section">
                <h3 class="template-detail-section__title">{{ __('ui.assets') }}</h3>
                <div class="template-detail-grid">
                    <div class="template-detail-item">
                        <span class="template-detail-item__label">{{ $header->isTransfer() ? __('templates.fields.source_asset') : __('templates.fields.asset') }}</span>
                        <span class="template-detail-item__value">{{ $header->asset->name ?? __('ui.none') }}</span>
                    </div>
                    @if($header->isTransfer() && $header->destinationAsset)
                        <div class="template-detail-item">
                            <span class="template-detail-item__label">{{ __('templates.fields.destination_asset') }}</span>
                            <span class="template-detail-item__value">{{ $header->destinationAsset->name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="template-detail-actions">
                <a href="{{ url('/templates') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> {{ __('ui.back_to_list', ['item' => __('templates.title')]) }}
                </a>
                <a href="{{ url('/templates/' . $header->id . '/edit') }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> {{ __('ui.edit') }}
                </a>
                <a href="{{ url('/templates/' . $header->id . '/delete') }}" class="btn btn-danger">
                    <i class="bi bi-trash"></i> {{ __('ui.delete') }}
                </a>
            </div>
        </div>
    </div>
@endsection
