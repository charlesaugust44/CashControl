@extends('layouts.auth')

@section('content')
<h2 class="auth-title">{{ __('auth.register') }}</h2>

<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label">{{ __('auth.name') }}</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('auth.email') }}</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
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

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">{{ __('auth.confirm_password') }}</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">{{ __('auth.register') }}</button>
</form>

<div class="auth-links">
    <a href="{{ route('login') }}">{{ __('auth.has_account') }}</a>
</div>
@endsection
