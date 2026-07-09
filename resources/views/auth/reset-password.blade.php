@extends('layouts.auth')

@section('content')
<h2 class="auth-title">{{ __('auth.reset_password') }}</h2>

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('auth.email') }}</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $email) }}" required autofocus>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">{{ __('auth.new_password') }}</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">{{ __('auth.confirm_password') }}</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">{{ __('auth.reset_password') }}</button>
</form>
@endsection
