@props([
    'post' => null,
])

@php
    $postId = $post?->id;
    $uploadUrl = $postId
        ? route('admin.posts.inline-images.store', $post)
        : null;
@endphp

<div
    x-data="imagePickerModal({
        postId: {{ $postId !== null ? (int) $postId : 'null' }},
        browseUrl: '{{ route('admin.media-picker.index') }}',
        uploadUrl: {{ $uploadUrl ? "'".$uploadUrl."'" : 'null' }},
        csrfToken: '{{ csrf_token() }}',
    })"
    x-show="$store.imagePicker.open"
    x-cloak
    x-transition.opacity
    @keydown.escape.window="$store.imagePicker.close()"
    class="image-picker-backdrop"
    role="dialog"
    aria-modal="true"
    aria-label="Afbeelding kiezen"
>
    <div class="image-picker-modal" @click.outside="$store.imagePicker.close()">
        <header class="image-picker-modal__header">
            <h2 class="image-picker-modal__title">Afbeelding kiezen</h2>
            <button type="button" class="image-picker-modal__close"
                @click="$store.imagePicker.close()"
                aria-label="Sluiten">
                <i class="bi bi-x-lg"></i>
            </button>
        </header>

        <nav class="image-picker-modal__tabs" role="tablist">
            <button type="button" class="image-picker-modal__tab"
                :class="{ 'is-active': activeTab === 'browse' }"
                @click="activeTab = 'browse'"
                role="tab">
                <i class="bi bi-grid-3x3-gap"></i>
                Bladeren
            </button>
            <button type="button" class="image-picker-modal__tab"
                :class="{ 'is-active': activeTab === 'upload' }"
                :disabled="!canUpload"
                @click="activeTab = 'upload'"
                role="tab"
                :title="canUpload ? 'Nieuwe afbeelding uploaden' : 'Sla de post eerst op als concept om te kunnen uploaden'">
                <i class="bi bi-cloud-upload"></i>
                Uploaden
                <span x-show="!canUpload" class="image-picker-modal__tab-hint">(sla eerst op)</span>
            </button>
        </nav>

        {{-- ─── Browse tab ──────────────────────────────────────────── --}}
        <section x-show="activeTab === 'browse'" class="image-picker-modal__body">
            <div class="image-picker-modal__filters">
                <input type="search"
                    class="form-control"
                    placeholder="Zoeken op naam of context..."
                    x-model.debounce.400ms="search"
                    @input.debounce.400ms="applyFilters()">

                <select class="form-control" x-model="collectionFilter" @change="applyFilters()">
                    <option value="">Alle collecties</option>
                    <option value="gallery">Galerij</option>
                    <option value="hero">Hero-afbeelding</option>
                    <option value="featured">Uitgelichte afbeelding</option>
                    <option value="inline_images">Inline (in posts)</option>
                </select>
            </div>

            <div class="image-picker-modal__grid"
                x-show="!loading || items.length > 0">
                <template x-for="item in items" :key="item.id">
                    <button type="button" class="image-picker-modal__item"
                        @click="pickItem(item)">
                        <img :src="item.thumb_url" :alt="item.alt || ''" loading="lazy">
                        <span class="image-picker-modal__item-label" x-text="item.context"></span>
                    </button>
                </template>
            </div>

            <div x-show="loading" class="image-picker-modal__loading">
                <i class="bi bi-arrow-repeat"></i> Afbeeldingen laden...
            </div>

            <p x-show="!loading && hasFetched && items.length === 0"
                class="image-picker-modal__empty">
                Geen afbeeldingen gevonden.
            </p>

            <p x-show="loadError" x-text="loadError"
                class="image-picker-modal__error" role="alert"></p>

            <div x-show="nextCursor && !loading" class="image-picker-modal__load-more">
                <button type="button" class="btn btn-outline-secondary" @click="loadMore()">
                    Meer laden
                </button>
            </div>
        </section>

        {{-- ─── Upload tab ──────────────────────────────────────────── --}}
        <section x-show="activeTab === 'upload'" class="image-picker-modal__body">
            <template x-if="!canUpload">
                <div class="image-picker-modal__upload-disabled">
                    <i class="bi bi-info-circle"></i>
                    Sla de post eerst op als concept. Daarna kun je hier nieuwe afbeeldingen uploaden die alleen bij deze post horen.
                </div>
            </template>

            <template x-if="canUpload">
                <div>
                    <div class="image-picker-modal__dropzone"
                        :class="{ 'is-dragging': dragging, 'is-uploading': uploading }"
                        @dragover.prevent="onDragOver($event)"
                        @dragleave.prevent="onDragLeave()"
                        @drop.prevent="onDrop($event)">

                        <template x-if="!uploading">
                            <div>
                                <i class="bi bi-cloud-upload image-picker-modal__dropzone-icon"></i>
                                <p>Sleep een afbeelding hierheen, of</p>
                                <label class="btn btn-primary">
                                    Kies bestand
                                    <input type="file" accept="image/jpeg,image/png,image/webp"
                                        @change="onFilePick($event)" hidden>
                                </label>
                                <p class="image-picker-modal__dropzone-hint">
                                    JPEG, PNG of WebP, max. 16 MB
                                </p>
                            </div>
                        </template>

                        <template x-if="uploading">
                            <div class="image-picker-modal__uploading">
                                <i class="bi bi-arrow-repeat"></i>
                                <p>Uploaden...</p>
                            </div>
                        </template>
                    </div>

                    <div class="image-picker-modal__alt">
                        <label for="image-picker-alt">Alt-tekst (optioneel, voor toegankelijkheid &amp; SEO)</label>
                        <input id="image-picker-alt" type="text" class="form-control"
                            x-model="uploadAlt" placeholder="Beschrijf wat er op de foto staat">
                    </div>

                    <p x-show="uploadError" x-text="uploadError"
                        class="image-picker-modal__error" role="alert"></p>
                </div>
            </template>
        </section>
    </div>
</div>
