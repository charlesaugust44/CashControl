@extends('layouts.auth')

@section('content')
<div class="auth-status">
    <i class="bi bi-exclamation-triangle auth-status__icon auth-status__icon--danger"></i>
    <h2 class="auth-title">{{ __('auth.no_unity') }}</h2>
    <p class="auth-description">{{ __('auth.no_unity_description') }}</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary">{{ __('auth.logout') }}</button>
    </form>
</div>
@endsection
