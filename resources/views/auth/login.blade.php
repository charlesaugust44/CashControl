@extends('layouts.auth')

@section('content')
<h2 class="auth-title">{{ __('auth.login') }}</h2>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('auth.email') }}</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">{{ __('auth.password') }}</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember">{{ __('auth.remember_me') }}</label>
    </div>

    <button type="submit" class="btn btn-primary w-100">{{ __('auth.login') }}</button>
</form>

<div class="auth-links">
    <a href="{{ route('password.request') }}">{{ __('auth.forgot_password') }}</a>
    <a href="{{ route('register') }}">{{ __('auth.no_account') }}</a>
</div>
@endsection
