@extends('layouts.app')

@section('content')
    <div class="admin-page">
        <div class="admin-scroll">
            <div class="admin-inner">
                <div class="admin-page-header">
                    <div class="admin-page-header__left">
                        <a href="{{ route('admin.unities.index') }}" class="admin-page-header__back">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div class="admin-page-header__info">
                            <h1 class="admin-page-header__title">{{ $unity->name }}</h1>
                            @if($unity->description)
                                <p class="admin-page-header__subtitle">{{ $unity->description }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="admin-page-header__actions">
                        <a href="{{ route('admin.unities.edit', $unity->id) }}" class="admin-btn admin-btn--secondary">
                            <i class="bi bi-pencil"></i>
                            {{ __('admin.unities.edit') }}
                        </a>
                    </div>
                </div>

                <div class="admin-detail-section">
                    <div class="admin-stats-grid">
                        <div class="admin-stat">
                            <span class="admin-stat__value">{{ $unity->users->count() }}</span>
                            <span class="admin-stat__label">{{ __('admin.unities.users') }}</span>
                        </div>
                        <div class="admin-stat">
                            <span class="admin-stat__value">{{ $unity->assets->count() }}</span>
                            <span class="admin-stat__label">{{ __('admin.unities.assets') }}</span>
                        </div>
                        <div class="admin-stat">
                            <span class="admin-stat__value">{{ $unity->headers->count() }}</span>
                            <span class="admin-stat__label">{{ __('admin.unities.templates') }}</span>
                        </div>
                        <div class="admin-stat">
                            <span class="admin-stat__value">{{ $unity->events->count() }}</span>
                            <span class="admin-stat__label">{{ __('admin.unities.events') }}</span>
                        </div>
                    </div>
                </div>

                <div class="admin-detail-section">
                    <div class="admin-card">
                        <div class="admin-card__header">
                            <h2 class="admin-card__title">
                                <i class="bi bi-people"></i>
                                {{ __('admin.unities.assigned_users') }}
                                <span class="admin-card__count">{{ $unity->users->count() }}</span>
                            </h2>
                        </div>
                        <div class="admin-card__body">
                            @if($unity->users->isEmpty())
                                <p style="font-size: 0.8125rem; color: var(--color-text-muted); margin: 0;">
                                    {{ __('admin.unities.no_users_assigned') }}
                                </p>
                            @else
                                <div class="admin-user-list">
                                    @foreach($unity->users as $user)
                                        <div class="admin-user-card">
                                            <div class="admin-user-card__avatar">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div class="admin-user-card__info">
                                                <p class="admin-user-card__name">{{ $user->name }}</p>
                                                <p class="admin-user-card__email">{{ $user->email }}</p>
                                            </div>
                                            @if($user->role === 'admin')
                                                <span class="admin-badge admin-badge--admin">{{ __('admin.roles.admin') }}</span>
                                            @endif
                                            <div class="admin-user-card__actions">
                                                <a href="{{ route('admin.users.show', $user->id) }}" class="admin-btn admin-btn--ghost admin-btn--sm" title="{{ __('admin.users.view') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <form action="{{ route('admin.unities.unassign', [$unity->id, $user->id]) }}" method="POST" onsubmit="return confirm('{{ __('admin.unities.confirm_unassign') }}')">
                                                    @csrf
                                                    <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" style="color: var(--color-danger);" title="{{ __('admin.unities.unassign') }}">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($availableUsers->count() > 0)
                    <div class="admin-detail-section">
                        <div class="admin-card">
                            <div class="admin-card__header">
                                <h2 class="admin-card__title">
                                    <i class="bi bi-person-plus"></i>
                                    {{ __('admin.unities.assign_user') }}
                                </h2>
                            </div>
                            <div class="admin-card__body">
                                <form action="{{ route('admin.unities.assign', $unity->id) }}" method="POST">
                                    @csrf
                                    <div class="admin-form-group" style="margin-bottom: var(--space-4);">
                                        <label for="user_id" class="admin-form-label">{{ __('admin.unities.select_user') }}</label>
                                        <select class="admin-select" id="user_id" name="user_id" required>
                                            <option value="">{{ __('admin.unities.choose_user') }}</option>
                                            @foreach($availableUsers as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="admin-btn admin-btn--primary">
                                        <i class="bi bi-person-plus"></i>
                                        {{ __('admin.unities.assign') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
