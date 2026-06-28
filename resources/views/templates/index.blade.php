@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/templates.css'])
@endpush

@section('content')
    <div class="templates-container">
        <div class="templates-header">
            <h2 class="templates-title">Templates</h2>
            <a href="{{ url('/templates/create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                <span>New Template</span>
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($headers->isEmpty())
            <div class="empty-state">
                <i class="bi bi-clipboard-data empty-state__icon"></i>
                <h3 class="empty-state__title">No templates yet</h3>
                <p class="empty-state__text">Create your first template to start generating recurring events.</p>
                <a href="{{ url('/templates/create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    <span>Create Template</span>
                </a>
            </div>
        @else
            <div class="templates-list">
                @foreach($headers as $header)
                    <a href="{{ url('/templates/' . $header->id) }}" class="template-card">
                        <div class="template-card__header">
                            <div class="template-card__title-row">
                                @php
                                    $typeIcons = ['income' => 'bi-arrow-down-left', 'expense' => 'bi-arrow-up-right', 'transfer' => 'bi-arrow-left-right'];
                                    $typeIcon = $typeIcons[$header->type->value] ?? 'bi-tag';
                                @endphp
                                <i class="{{ $typeIcon }} template-card__type-icon type-{{ $header->type->value }}"></i>
                                <h3 class="template-card__name">{{ $header->name }}</h3>
                            </div>
                            <span class="template-type-badge type-{{ $header->type->value }}">{{ $header->type->value }}</span>
                        </div>

                        @if($header->description)
                            <p class="template-card__description">{{ $header->description }}</p>
                        @endif

                        <div class="template-card__details">
                            <div class="template-card__detail">
                                <i class="bi bi-calculator"></i>
                                <span>{{ str_replace('_', ' ', $header->rule->value) }}</span>
                            </div>
                            @if($header->rule->value === 'fixed' && $header->default_amount)
                                <div class="template-card__detail">
                                    <i class="bi bi-currency-dollar"></i>
                                    <span>{{ number_format($header->default_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="template-card__detail">
                                <i class="bi bi-wallet2"></i>
                                <span>{{ $header->asset->name ?? 'No asset' }}</span>
                            </div>
                            @if($header->isTransfer() && $header->destinationAsset)
                                <div class="template-card__detail">
                                    <i class="bi bi-arrow-right"></i>
                                    <span>{{ $header->destinationAsset->name }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="template-card__dates">
                            <span>{{ $header->start_date->format('M Y') }}</span>
                            <i class="bi bi-arrow-right"></i>
                            <span>{{ $header->end_date ? $header->end_date->format('M Y') : 'Ongoing' }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
