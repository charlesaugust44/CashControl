@extends('layouts.app')

@section('content')
    <div class="admin-page">
        <div class="admin-scroll">
            <div class="admin-inner">
                <div class="admin-page-header">
                    <div class="admin-page-header__info">
                        <h1 class="admin-page-header__title">{{ __('admin.backups.title') }}</h1>
                        <p class="admin-page-header__subtitle">
                            @if($isConnected)
                                <span class="admin-badge admin-badge--approved">
                                    <i class="bi bi-google"></i>
                                    {{ __('admin.backups.drive_connected') }}
                                </span>
                            @else
                                <span class="admin-badge admin-badge--pending">
                                    <i class="bi bi-google"></i>
                                    {{ __('admin.backups.drive_not_connected') }}
                                </span>
                            @endif
                        </p>
                    </div>
                    <div class="admin-page-header__actions">
                        @if($isConnected)
                            <form action="{{ route('admin.backups.store') }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="admin-btn admin-btn--primary">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    {{ __('admin.backups.create_backup') }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('admin.backups.connect') }}" class="admin-btn admin-btn--primary">
                                <i class="bi bi-google"></i>
                                {{ __('admin.backups.connect_drive') }}
                            </a>
                        @endif
                    </div>
                </div>

                @if(!$isConnected)
                    <div class="admin-card">
                        <div class="admin-empty">
                            <i class="bi bi-cloud-slash admin-empty__icon"></i>
                            <h3 class="admin-empty__title">{{ __('admin.backups.not_connected_title') }}</h3>
                            <p class="admin-empty__text">{{ __('admin.backups.not_connected_description') }}</p>
                        </div>
                    </div>
                @elseif($backups->isEmpty())
                    <div class="admin-card">
                        <div class="admin-empty">
                            <i class="bi bi-archive admin-empty__icon"></i>
                            <h3 class="admin-empty__title">{{ __('admin.backups.no_backups') }}</h3>
                            <p class="admin-empty__text">{{ __('admin.backups.no_backups_description') }}</p>
                        </div>
                    </div>
                @else
                    <div class="admin-card">
                        <div class="admin-card__body admin-card__body--flush">
                            <div class="admin-user-list" style="padding: var(--space-2);">
                                @foreach($backups as $backup)
                                    <div class="admin-user-card">
                                        <div class="admin-user-card__avatar" style="background: var(--color-success-bg); color: var(--color-success);">
                                            <i class="bi bi-archive"></i>
                                        </div>
                                        <div class="admin-user-card__info">
                                            <p class="admin-user-card__name">{{ $backup->filename }}</p>
                                            <p class="admin-user-card__email">
                                                {{ \Carbon\Carbon::parse($backup->created_at)->diffForHumans() }}
                                                &middot;
                                                {{ number_format($backup->size_bytes / 1024, 1) }} KB
                                            </p>
                                        </div>
                                        <div class="admin-user-card__badges">
                                            @if($backup->status === 'success')
                                                <span class="admin-badge admin-badge--approved">
                                                    <i class="bi bi-check-circle"></i>
                                                    {{ __('admin.backups.status_success') }}
                                                </span>
                                            @else
                                                <span class="admin-badge admin-badge--pending">
                                                    <i class="bi bi-x-circle"></i>
                                                    {{ __('admin.backups.status_failed') }}
                                                </span>
                                            @endif
                                            <span class="admin-badge admin-badge--common">
                                                {{ $backup->trigger_type }}
                                            </span>
                                        </div>
                                        <div class="admin-user-card__actions">
                                            @if($backup->status === 'success' && $backup->drive_file_id)
                                                <form action="{{ route('admin.backups.restore', $backup->id) }}" method="POST" style="display:inline" onsubmit="return confirmRestore(event, '{{ \Carbon\Carbon::parse($backup->created_at)->format('Y-m-d') }}')">
                                                    @csrf
                                                    <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" style="color: var(--color-warning);" title="{{ __('admin.backups.restore') }}">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.backups.destroy', $backup->id) }}" method="POST" style="display:inline" onsubmit="return confirm('{{ __('admin.backups.confirm_delete') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" style="color: var(--color-danger);" title="{{ __('admin.backups.delete') }}">
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

                @if($isConnected)
                    <div style="margin-top: var(--space-6);">
                        <form action="{{ route('admin.backups.disconnect') }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn admin-btn--secondary" onsubmit="return confirm('{{ __('admin.backups.confirm_disconnect') }}')">
                                <i class="bi bi-google"></i>
                                {{ __('admin.backups.disconnect_drive') }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="restoreConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('admin.backups.confirm_restore_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('admin.backups.confirm_restore_warning') }}</p>
                    <p class="mb-2">{{ __('admin.backups.confirm_restore_instruction') }}</p>
                    <p class="fw-bold text-danger" id="restoreConfirmText"></p>
                    <input type="text" class="form-control" id="restoreConfirmInput" autocomplete="off">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.backups.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="restoreConfirmButton" disabled>
                        <i class="bi bi-arrow-counterclockwise"></i>
                        {{ __('admin.backups.restore') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/restore-confirm.js'])
@endpush
