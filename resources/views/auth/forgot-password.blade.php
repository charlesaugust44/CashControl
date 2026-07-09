@extends('layouts.auth')

@section('content')
<h2 class="auth-title">{{ __('auth.forgot_password') }}</h2>

<p class="auth-description">{{ __('auth.forgot_password_description') }}</p>

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('auth.email') }}</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100">{{ __('auth.send_reset_link') }}</button>
</form>

<div class="auth-links">
    <a href="{{ route('login') }}">{{ __('auth.back_to_login') }}</a>
</div>
@endsection
