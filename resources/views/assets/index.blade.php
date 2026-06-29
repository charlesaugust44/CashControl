@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pages/assets.css'])
@endpush

@section('content')
    <div class="assets-container">
        @component('components.asset-header', ['total' => $total, 'actionUrl' => url('/assets/create')])
            <i class="bi bi-plus-circle"></i>
            <span>{{ __('ui.new', ['item' => __('assets.singular')]) }}</span>
        @endcomponent
        <div class="list-wrapper">
            @foreach($assets as $asset)
                <div class="list-item">
                    @include('components.asset-item', ['asset' => $asset])
                </div>
            @endforeach
        </div>
    </div>
@endsection
