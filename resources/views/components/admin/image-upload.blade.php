@props([
    'name',
    'label' => null,
    'currentUrl' => null,
    'hint' => null,
    'shape' => 'square',       // 'square' | 'circle'
    'accept' => 'image/jpeg,image/png,image/webp',
    'maxMb' => 8,
    'minWidth' => 300,
    'minHeight' => 300,
    'removeLabel' => null,
])

@php
    $id = 'imgupload-' . $name;
    $errorKey = $name;
    $hasCurrent = ! empty($currentUrl);
@endphp

<div
    class="image-upload image-upload--{{ $shape }}"
    x-data="imageUpload({
        inputId: '{{ $id }}',
        maxBytes: {{ $maxMb * 1024 * 1024 }},
        minWidth: {{ $minWidth }},
        minHeight: {{ $minHeight }},
        accept: @js(explode(',', $accept)),
        hasCurrent: {{ $hasCurrent ? 'true' : 'false' }},
        currentUrl: @js($currentUrl),
    })"
>
    @if ($label)
        <label for="{{ $id }}" class="admin-field__label">{{ $label }}</label>
    @endif

    {{-- Drop-zone --}}
    <div
        class="image-upload__zone"
        :class="{
            'image-upload__zone--dragging': dragging,
            'image-upload__zone--has-preview': previewUrl || (hasCurrent && !markedForRemoval),
            'image-upload__zone--error': !!clientError,
        }"
        @click="!previewUrl && !hasCurrent && $refs.input.click()"
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="handleDrop($event)"
        role="button"
        tabindex="0"
        @keydown.enter.prevent="!previewUrl && !hasCurrent && $refs.input.click()"
        @keydown.space.prevent="!previewUrl && !hasCurrent && $refs.input.click()"
    >
        {{-- Preview --}}
        <template x-if="previewUrl">
            <div class="image-upload__preview">
                <img :src="previewUrl" alt="" class="image-upload__preview-image">
            </div>
        </template>

        <template x-if="!previewUrl && hasCurrent && !markedForRemoval">
            <div class="image-upload__preview">
                <img :src="currentUrl" alt="" class="image-upload__preview-image">
            </div>
        </template>

        {{-- Placeholder bij geen preview --}}
        <template x-if="!previewUrl && (!hasCurrent || markedForRemoval)">
            <div class="image-upload__placeholder">
                <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
                <p class="image-upload__placeholder-title">
                    {{ __('Sleep een foto hierheen of klik om te kiezen') }}
                </p>
                <p class="image-upload__placeholder-meta">
                    {{ __(':types tot :max MB · minimaal :w×:h px', [
                        'types' => 'JPG, PNG, WebP',
                        'max' => $maxMb,
                        'w' => $minWidth,
                        'h' => $minHeight,
                    ]) }}
                </p>
            </div>
        </template>
    </div>

    {{-- Action-buttons onder de zone --}}
    <div class="image-upload__actions" x-show="previewUrl || (hasCurrent && !markedForRemoval)">
        <button
            type="button"
            class="btn btn-sm btn-outline-secondary"
            @click="$refs.input.click()"
        >
            <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
            <span x-text="previewUrl ? '{{ __('Andere foto kiezen') }}' : '{{ __('Vervangen') }}'"></span>
        </button>

        {{-- Bij geselecteerde nieuwe foto (nog niet opgeslagen) --}}
        <button
            type="button"
            class="btn btn-sm btn-link text-danger"
            x-show="previewUrl"
            @click="clearSelection()"
        >
            <i class="bi bi-x-lg" aria-hidden="true"></i>
            {{ __('Selectie wissen') }}
        </button>

        {{-- Bij bestaande opgeslagen foto: checkbox om te verwijderen --}}
        <label class="image-upload__remove-toggle" x-show="hasCurrent && !previewUrl">
            <input
                type="checkbox"
                name="remove_{{ $name }}"
                value="1"
                x-model="markedForRemoval"
            >
            <<span class="text-danger">{{ $removeLabel ?? __('Portret verwijderen bij opslaan') }}</span>
        </label>
    </div>

    {{-- Verborgen file-input --}}
    <input
        type="file"
        id="{{ $id }}"
        name="{{ $name }}"
        x-ref="input"
        @change="handleFileChange($event)"
        accept="{{ $accept }}"
        class="image-upload__input"
        hidden
    >

    {{-- Client-side error --}}
    <p class="admin-field__error" x-show="clientError" x-text="clientError"></p>

    {{-- Server-side error --}}
    @error($errorKey)
        <span class="admin-field__error">{{ $message }}</span>
    @enderror

    @if ($hint)
        <small class="admin-field__hint">{{ $hint }}</small>
    @endif
</div>
