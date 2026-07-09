@extends('layouts.app')

@section('content')
    <div class="admin-page">
        <div class="admin-scroll">
            <div class="admin-inner">
                <div class="admin-page-header">
                    <div class="admin-page-header__left">
                        <a href="{{ route('admin.users.index') }}" class="admin-page-header__back">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div class="admin-page-header__info">
                            <h1 class="admin-page-header__title">{{ $user->name }}</h1>
                            <p class="admin-page-header__subtitle">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="admin-page-header__actions">
                        @if($user->isApproved())
                            <span class="admin-badge admin-badge--approved">
                                <i class="bi bi-check-circle"></i>
                                {{ __('admin.users.approved') }}
                            </span>
                        @else
                            <span class="admin-badge admin-badge--pending">
                                <i class="bi bi-hourglass-split"></i>
                                {{ __('admin.users.pending') }}
                            </span>
                        @endif
                        <span class="admin-badge admin-badge--{{ $user->role }}">
                            {{ __('admin.roles.' . $user->role) }}
                        </span>
                    </div>
                </div>

                <div class="admin-detail-section">
                    <div class="admin-card">
                        <div class="admin-card__header">
                            <h2 class="admin-card__title">
                                <i class="bi bi-info-circle"></i>
                                {{ __('admin.users.account_info') }}
                            </h2>
                        </div>
                        <div class="admin-card__body">
                            <div class="admin-detail-grid">
                                <div class="admin-detail-field">
                                    <span class="admin-detail-label">{{ __('admin.users.registered') }}</span>
                                    <span class="admin-detail-value">{{ $user->created_at->translatedFormat('d M Y, H:i') }}</span>
                                </div>
                                @if($user->isApproved())
                                    <div class="admin-detail-field">
                                        <span class="admin-detail-label">{{ __('admin.users.approved_at') }}</span>
                                        <span class="admin-detail-value">{{ $user->approved_at->translatedFormat('d M Y, H:i') }}</span>
                                    </div>
                                    @if($user->approvedBy)
                                        <div class="admin-detail-field">
                                            <span class="admin-detail-label">{{ __('admin.users.approved_by') }}</span>
                                            <span class="admin-detail-value">{{ $user->approvedBy->name }}</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin-detail-section">
                    <div class="admin-card">
                        <div class="admin-card__header">
                            <h2 class="admin-card__title">
                                <i class="bi bi-building"></i>
                                {{ __('admin.users.unity_assignment') }}
                            </h2>
                        </div>
                        <div class="admin-card__body">
                            @if($user->unities->count() > 0)
                                <div class="admin-detail-grid">
                                    @foreach($user->unities as $unity)
                                        <div class="admin-detail-field">
                                            <span class="admin-detail-label">{{ __('admin.users.unity') }}</span>
                                            <span class="admin-detail-value">
                                                <a href="{{ route('admin.unities.show', $unity->id) }}" style="color: var(--color-primary); text-decoration: none;">
                                                    {{ $unity->name }}
                                                </a>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p style="font-size: 0.8125rem; color: var(--color-text-muted); margin: 0;">
                                    {{ __('admin.users.no_unity_assigned') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                @if($user->id !== auth()->id())
                    <div class="admin-detail-section">
                        <div class="admin-card">
                            <div class="admin-card__header">
                                <h2 class="admin-card__title">
                                    <i class="bi bi-gear"></i>
                                    {{ __('admin.users.actions') }}
                                </h2>
                            </div>
                            <div class="admin-card__body">
                                <div style="display: flex; flex-direction: column; gap: var(--space-5);">
                                    @if(!$user->isApproved())
                                        <form action="{{ route('admin.users.approve', $user->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="admin-btn admin-btn--success">
                                                <i class="bi bi-check-lg"></i>
                                                {{ __('admin.users.approve') }}
                                            </button>
                                        </form>
                                    @endif

                                    <div>
                                        <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <label class="admin-form-label">{{ __('admin.users.change_role') }}</label>
                                            <div style="display: flex; gap: var(--space-2); align-items: center;">
                                                <select name="role" class="admin-select" style="max-width: 200px;">
                                                    <option value="common" @if($user->role === 'common') selected @endif>{{ __('admin.roles.common') }}</option>
                                                    <option value="admin" @if($user->role === 'admin') selected @endif>{{ __('admin.roles.admin') }}</option>
                                                </select>
                                                <button type="submit" class="admin-btn admin-btn--primary">
                                                    {{ __('admin.users.update_role') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <div style="padding-top: var(--space-4); border-top: 1px solid var(--color-border-light);">
                                        <form action="{{ route('admin.users.reject', $user->id) }}" method="POST" onsubmit="return confirm('{{ __('admin.users.confirm_reject') }}')">
                                            @csrf
                                            <button type="submit" class="admin-btn admin-btn--danger">
                                                <i class="bi bi-x-lg"></i>
                                                {{ __('admin.users.reject_delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
