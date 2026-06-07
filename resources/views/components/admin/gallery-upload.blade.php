@props([
    'name' => 'gallery',
    'model',
    'label' => null,
    'maxMb' => 16,
    'accept' => 'image/jpeg,image/png,image/webp',
])

@php
    $type = array_search(get_class($model), config('westein.gallery_models', []), true);

    $items = $model->getMedia($name)->map(fn ($m) => [
        'id' => $m->id,
        'url' => $m->hasGeneratedConversion('thumb') ? $m->getUrl('thumb') : $m->getUrl(),
        'name' => $m->file_name,
    ])->values();
@endphp

<div
    class="gallery-upload"
    x-data="galleryUpload({
        modelType: @js($type),
        modelId: {{ $model->getKey() }},
        collection: @js($name),
        uploadUrl: @js(route('admin.media.upload')),
        reorderUrl: @js(route('admin.media.reorder')),
        destroyUrlBase: @js(url('admin/media') . '/'),
        maxBytes: {{ $maxMb * 1024 * 1024 }},
        accept: @js(explode(',', $accept)),
        items: @js($items),
    })"
>
    @if ($label)
        <label class="admin-field__label">{{ $label }}</label>
    @endif

    {{-- Upload drop-zone --}}
    <div
        class="gallery-upload__dropzone"
        :class="{ 'gallery-upload__dropzone--dragging': dragging }"
        @click="openPicker()"
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="handleDrop($event)"
        role="button"
        tabindex="0"
        @keydown.enter.prevent="openPicker()"
    >
        <i class="bi bi-images" aria-hidden="true"></i>
        <p class="gallery-upload__dropzone-title">
            {{ __('Sleep foto\'s hierheen of klik om te kiezen') }}
        </p>
        <p class="gallery-upload__dropzone-meta">
            {{ __('JPG, PNG, WebP · meerdere tegelijk mogelijk') }}
        </p>
        <div x-show="uploading" class="gallery-upload__uploading" x-cloak>
            <i class="bi bi-arrow-repeat" aria-hidden="true"></i> {{ __('Bezig met uploaden…') }}
        </div>
    </div>

    <input
        type="file"
        x-ref="input"
        @change="handleFileChange($event)"
        accept="{{ $accept }}"
        multiple
        hidden
    >

    <p class="admin-field__error" x-show="error" x-text="error" x-cloak></p>

    {{-- Thumbnail-grid (sorteerbaar) --}}
    <div class="gallery-upload__grid" x-ref="grid">
        <template x-for="item in items" :key="item.id">
            <figure class="gallery-upload__item" :data-media-id="item.id">
                <button type="button" class="gallery-upload__handle" title="{{ __('Versleep om te ordenen') }}">
                    <i class="bi bi-grip-vertical" aria-hidden="true"></i>
                </button>

                <img :src="item.url" :alt="item.name" class="gallery-upload__thumb" loading="lazy">

                {{-- Delete-knop + inline confirm --}}
                <div class="gallery-upload__actions">
                    <button
                        type="button"
                        class="gallery-upload__delete"
                        x-show="confirmingId !== item.id"
                        @click="askDelete(item.id)"
                        title="{{ __('Verwijderen') }}"
                    >
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                    <div class="gallery-upload__confirm" x-show="confirmingId === item.id" x-cloak>
                        <button type="button" class="btn btn-sm btn-danger" @click="confirmDelete(item.id)">
                            {{ __('Weg') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="cancelDelete()">
                            {{ __('Annuleer') }}
                        </button>
                    </div>
                </div>
            </figure>
        </template>
    </div>

    <p class="gallery-upload__empty" x-show="items.length === 0" x-cloak>
        {{ __('Nog geen foto\'s in de galerij.') }}
    </p>
</div>
