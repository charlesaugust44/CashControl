@extends('layouts.auth')

@section('content')
<div class="auth-status">
    <i class="bi bi-hourglass-split auth-status__icon auth-status__icon--warning"></i>
    <h2 class="auth-title">{{ __('auth.pending_approval') }}</h2>
    <p class="auth-description">{{ __('auth.pending_approval_description') }}</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary">{{ __('auth.logout') }}</button>
    </form>
</div>
@endsection
