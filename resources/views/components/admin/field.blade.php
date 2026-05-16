@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'hint' => null,
    'readonly' => false,
])

@php
    $id = 'field-' . $name;
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
@endphp

<div class="admin-field">
    <label for="{{ $id }}" class="admin-field__label">
        {{ $label }}
        @if ($required)<span class="admin-field__required">*</span>@endif
    </label>

    @if ($type === 'textarea')
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            class="form-control @error($errorKey) is-invalid @enderror"
            {{ $attributes->except(['class']) }}
        >{{ old($name, $value) }}</textarea>
    @else
        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            {{ $required ? 'required' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            class="form-control @error($errorKey) is-invalid @enderror"
            {{ $attributes->except(['class']) }}
        >
    @endif

    @if ($hint)
        <small class="admin-field__hint">{{ $hint }}</small>
    @endif

    @error($errorKey)
        <span class="admin-field__error">{{ $message }}</span>
    @enderror
</div>
