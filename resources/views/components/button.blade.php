@props([
    'variant' => 'primary',
    'icon' => null,
    'label' => null,
    'href' => null,
    'type' => 'button',
    'name' => null,
    'value' => null,
    'form' => null,
    'formAction' => null,
    'formMethod' => null,
    'danger' => false,
])

@php
    $classes = 'btn btn-' . $variant;
    if ($danger) {
        $classes = 'btn btn-danger';
    }
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $classes }}" {{ $attributes }}>
        @if($icon)<i class="{{ $icon }}"></i>@endif
        {{ $label ?? $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        class="{{ $classes }}"
        @if($name) name="{{ $name }}" @endif
        @if($value) value="{{ $value }}" @endif
        @if($form) form="{{ $form }}" @endif
        @if($formAction) formaction="{{ $formAction }}" @endif
        @if($formMethod) formmethod="{{ $formMethod }}" @endif
        {{ $attributes }}
    >
        @if($icon)<i class="{{ $icon }}"></i>@endif
        {{ $label ?? $slot }}
    </button>
@endif
