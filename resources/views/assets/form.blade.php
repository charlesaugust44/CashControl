@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/asset-form.css'])
@endpush

@section('content')
    <form method="POST" action="{{ isset($asset) ? url("/assets/{$asset->id}") : url('/assets') }}" class="asset-form">
        @csrf
        @if(isset($asset))
            @method('PUT')
        @endif

        <div class="mb-3">
            <label for="assetName" class="form-label">Asset Name</label>
            <input
                type="text"
                class="form-control @error('name') is-invalid @enderror"
                id="assetName"
                name="name"
                value="{{ old('name', $asset->name ?? '') }}"
                placeholder="e.g., Checking Account"
            />
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="assetBalance" class="form-label">Initial Balance</label>
            <input
                type="number"
                step="0.01"
                class="form-control @error('balance') is-invalid @enderror"
                id="assetBalance"
                name="balance"
                value="{{ old('balance', $asset->balance ?? '') }}"
            />
            @error('balance')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ url('/assets') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
@endsection
