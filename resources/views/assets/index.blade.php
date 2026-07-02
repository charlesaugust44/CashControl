@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/assets.css'])
@endpush

@section('content')
    <div class="assets-container">
        @component('components.asset-header', [
            'total' => $total,
            'forecastedTotal' => $forecastedTotal,
            'currentMonthLabel' => $currentMonthLabel,
            'fmt' => $fmt,
        ])
        @endcomponent
        <div class="list-wrapper">
            @foreach($assets as $asset)
                <div class="list-item">
                    @include('components.asset-item', ['asset' => $asset, 'fmt' => $fmt])
                </div>
            @endforeach
        </div>
    </div>
@endsection
