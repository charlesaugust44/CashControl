@extends('layouts.app')

@section('content')
    <div class="admin-page">
        <div class="admin-scroll">
            <div class="admin-inner">
                <div class="admin-page-header">
                    <div class="admin-page-header__info">
                        <h1 class="admin-page-header__title">{{ __('admin.unities.title') }}</h1>
                        <p class="admin-page-header__subtitle">{{ $unities->count() }} {{ __('admin.unities.title') }}</p>
                    </div>
                    <div class="admin-page-header__actions">
                        <a href="{{ route('admin.unities.create') }}" class="admin-btn admin-btn--primary">
                            <i class="bi bi-plus-circle"></i>
                            {{ __('admin.unities.create') }}
                        </a>
                    </div>
                </div>

                @if($unities->isEmpty())
                    <div class="admin-card">
                        <div class="admin-empty">
                            <i class="bi bi-building admin-empty__icon"></i>
                            <h3 class="admin-empty__title">{{ __('admin.unities.no_unities') }}</h3>
                        </div>
                    </div>
                @else
                    <div class="admin-card">
                        <div class="admin-card__body admin-card__body--flush">
                            <div class="admin-unity-list" style="padding: var(--space-2);">
                                @foreach($unities as $unity)
                                    <div class="admin-unity-card">
                                        <div class="admin-unity-card__icon">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <div class="admin-unity-card__info">
                                            <p class="admin-unity-card__name">{{ $unity->name }}</p>
                                            @if($unity->description)
                                                <p class="admin-unity-card__desc">{{ $unity->description }}</p>
                                            @endif
                                        </div>
                                        <div class="admin-unity-card__stats">
                                            <div class="admin-unity-card__stat">
                                                <i class="bi bi-people"></i>
                                                <span class="admin-unity-card__stat-value">{{ $unity->users->count() }}</span>
                                            </div>
                                            <div class="admin-unity-card__stat">
                                                <i class="bi bi-wallet2"></i>
                                                <span class="admin-unity-card__stat-value">{{ $unity->assets->count() }}</span>
                                            </div>
                                        </div>
                                        <div class="admin-unity-card__actions">
                                            <a href="{{ route('admin.unities.show', $unity->id) }}" class="admin-btn admin-btn--ghost admin-btn--sm" title="{{ __('admin.unities.view') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.unities.edit', $unity->id) }}" class="admin-btn admin-btn--ghost admin-btn--sm" title="{{ __('admin.unities.edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.unities.destroy', $unity->id) }}" method="POST" style="display:inline" onsubmit="return confirm('{{ __('admin.unities.confirm_delete') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" style="color: var(--color-danger);" title="{{ __('admin.unities.delete') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
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
