@extends('layouts.app')

@section('content')
    <div class="admin-page">
        <div class="admin-scroll">
            <div class="admin-inner">
                <div class="admin-page-header">
                    <div class="admin-page-header__info">
                        <h1 class="admin-page-header__title">{{ __('admin.users.title') }}</h1>
                        <p class="admin-page-header__subtitle">{{ $users->count() }} {{ __('admin.users.total') }}</p>
                    </div>
                </div>

                @if($users->isEmpty())
                    <div class="admin-card">
                        <div class="admin-empty">
                            <i class="bi bi-people admin-empty__icon"></i>
                            <h3 class="admin-empty__title">{{ __('admin.users.no_users') }}</h3>
                            <p class="admin-empty__text">{{ __('admin.users.no_users_description') }}</p>
                        </div>
                    </div>
                @else
                    <div class="admin-card">
                        <div class="admin-card__body admin-card__body--flush">
                            <div class="admin-user-list" style="padding: var(--space-2);">
                                @foreach($users as $user)
                                    <div class="admin-user-card">
                                        <div class="admin-user-card__avatar">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="admin-user-card__info">
                                            <p class="admin-user-card__name">{{ $user->name }}</p>
                                            <p class="admin-user-card__email">{{ $user->email }}</p>
                                        </div>
                                        <div class="admin-user-card__badges">
                                            <span class="admin-badge admin-badge--{{ $user->role }}">
                                                {{ __('admin.roles.' . $user->role) }}
                                            </span>
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
                                        </div>
                                        <div class="admin-user-card__actions">
                                            <a href="{{ route('admin.users.show', $user->id) }}" class="admin-btn admin-btn--ghost admin-btn--sm" title="{{ __('admin.users.view') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if(!$user->isApproved())
                                                <form action="{{ route('admin.users.approve', $user->id) }}" method="POST" style="display:inline">
                                                    @csrf
                                                    <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" style="color: var(--color-success);" title="{{ __('admin.users.approve') }}">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.reject', $user->id) }}" method="POST" style="display:inline" onsubmit="return confirm('{{ __('admin.users.confirm_reject') }}')">
                                                    @csrf
                                                    <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" style="color: var(--color-danger);" title="{{ __('admin.users.reject') }}">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
