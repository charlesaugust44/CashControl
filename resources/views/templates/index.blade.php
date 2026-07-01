@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/templates.css'])
@endpush

@section('content')
    <div class="templates-container">
        @include('components.filter-tabs', [
            'filters' => [
                'all'      => ['label' => __('ui.all'),                  'icon' => 'bi-grid-3x3-gap'],
                'income'   => ['label' => __('templates.types.income'),  'icon' => 'bi-arrow-down-left'],
                'expense'  => ['label' => __('templates.types.expense'), 'icon' => 'bi-arrow-up-right'],
                'transfer' => ['label' => __('templates.types.transfer'),'icon' => 'bi-arrow-left-right'],
            ],
            'currentFilter' => $currentFilter,
        ])

        @if($headers->isEmpty())
            <div class="empty-state">
                <i class="bi bi-clipboard-data empty-state__icon"></i>
                <h3 class="empty-state__title">{{ __('templates.no_templates') }}</h3>
                <p class="empty-state__text">{{ __('templates.no_templates_description') }}</p>
                <a href="{{ url('/templates/create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    <span>{{ __('templates.create_first') }}</span>
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
                            <span class="template-type-badge type-{{ $header->type->value }}">{{ __('templates.types.' . $header->type->value) }}</span>
                        </div>

                        @if($header->description)
                            <p class="template-card__description">{{ $header->description }}</p>
                        @endif

                        <div class="template-card__details">
                            <div class="template-card__detail">
                                <i class="bi bi-calculator"></i>
                                <span>{{ __('templates.rules.' . $header->rule->value) }}</span>
                            </div>
                            @if($header->rule->value === 'fixed' && $header->default_amount)
                                <div class="template-card__detail">
                                    <span>{{ $fmt->currency($header->default_amount) }}</span>
                                </div>
                            @endif
                            <div class="template-card__detail">
                                <i class="bi bi-wallet2"></i>
                                <span>{{ $header->asset->name ?? __('ui.none') }}</span>
                            </div>
                            @if($header->isTransfer() && $header->destinationAsset)
                                <div class="template-card__detail">
                                    <i class="bi bi-arrow-right"></i>
                                    <span>{{ $header->destinationAsset->name }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="template-card__dates">
                            <span>{{ $header->start_date->translatedFormat('M Y') }}</span>
                            <i class="bi bi-arrow-right"></i>
                            <span>{{ $header->end_date ? $header->end_date->translatedFormat('M Y') : __('templates.schedule.ongoing') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
