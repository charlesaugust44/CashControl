@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/asset-form.css'])
@endpush

@section('content')
    <div class="asset-form-wrapper">
        <div class="asset-form-card">
            <h2 class="asset-form-card__title">{{ isset($asset) ? __('assets.edit') : __('assets.new') }}</h2>
            <form method="POST" action="{{ isset($asset) ? url("/assets/{$asset->id}") : url('/assets') }}" class="asset-form">
                @csrf
                @if(isset($asset))
                    @method('PUT')
                @endif

                <div class="mb-4">
                    <label for="assetName" class="form-label">{{ __('assets.fields.name') }}</label>
                    <input
                        type="text"
                        class="form-control @error('name') is-invalid @enderror"
                        id="assetName"
                        name="name"
                        value="{{ old('name', $asset->name ?? '') }}"
                        placeholder="{{ __('assets.placeholders.name') }}"
                    />
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="assetBalance" class="form-label">{{ __('assets.fields.balance') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">{{ $fmt->currencySymbol() }}</span>
                        <input
                            type="text"
                            inputmode="decimal"
                            autocomplete="off"
                            class="form-control money-input @error('balance') is-invalid @enderror"
                            id="assetBalance"
                            name="balance"
                            value="{{ old('balance', $asset->balance ?? '') }}"
                            placeholder="0.00"
                        />
                        @error('balance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <div class="form-actions__group">
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
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
