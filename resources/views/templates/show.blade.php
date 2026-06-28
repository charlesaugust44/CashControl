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
                <span class="template-type-badge type-{{ $header->type->value }}">{{ $header->type->value }}</span>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($header->description)
                <p class="template-detail-description">{{ $header->description }}</p>
            @endif

            <div class="template-detail-section">
                <h3 class="template-detail-section__title">Configuration</h3>
                <div class="template-detail-grid">
                    <div class="template-detail-item">
                        <span class="template-detail-item__label">Rule</span>
                        <span class="template-detail-item__value">{{ str_replace('_', ' ', $header->rule->value) }}</span>
                    </div>
                    @if($header->default_amount)
                        <div class="template-detail-item">
                            <span class="template-detail-item__label">Default Amount</span>
                            <span class="template-detail-item__value">${{ number_format($header->default_amount, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="template-detail-section">
                <h3 class="template-detail-section__title">Schedule</h3>
                <div class="template-detail-grid">
                    <div class="template-detail-item">
                        <span class="template-detail-item__label">Start Date</span>
                        <span class="template-detail-item__value">{{ $header->start_date->format('M Y') }}</span>
                    </div>
                    <div class="template-detail-item">
                        <span class="template-detail-item__label">End Date</span>
                        <span class="template-detail-item__value">{{ $header->end_date ? $header->end_date->format('M Y') : 'Ongoing' }}</span>
                    </div>
                </div>
            </div>

            <div class="template-detail-section">
                <h3 class="template-detail-section__title">Assets</h3>
                <div class="template-detail-grid">
                    <div class="template-detail-item">
                        <span class="template-detail-item__label">{{ $header->isTransfer() ? 'Source Asset' : 'Asset' }}</span>
                        <span class="template-detail-item__value">{{ $header->asset->name ?? 'Not set' }}</span>
                    </div>
                    @if($header->isTransfer() && $header->destinationAsset)
                        <div class="template-detail-item">
                            <span class="template-detail-item__label">Destination Asset</span>
                            <span class="template-detail-item__value">{{ $header->destinationAsset->name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="template-detail-actions">
                <a href="{{ url('/templates') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Templates
                </a>
                <a href="{{ url('/templates/' . $header->id . '/edit') }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ url('/templates/' . $header->id . '/delete') }}" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Delete
                </a>
            </div>
        </div>
    </div>
@endsection
