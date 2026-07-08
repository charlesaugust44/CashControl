@props([
    'action',
    'title',
    'message',
])

<div class="delete-modal" id="deleteModal">
    <div class="delete-modal__backdrop" data-delete-modal-close></div>
    <div class="delete-modal__dialog">
        <div class="delete-modal__header">
            <i class="bi bi-exclamation-triangle-fill delete-modal__icon"></i>
            <h2 class="delete-modal__title">{{ $title }}</h2>
        </div>
        <p class="delete-modal__message">{!! $message !!}</p>

        <form method="POST" action="{{ $action }}" class="delete-modal__form">
            @csrf
            @method('DELETE')

            {{ $slot }}

            <div class="delete-modal__actions">
                <button type="button" class="btn btn-outline-secondary" data-delete-modal-close>
                    {{ __('ui.cancel') }}
                </button>
                <button type="submit" class="btn btn-danger">
                    {{ __('ui.delete') }}
                </button>
            </div>
        </form>
    </div>
</div>
