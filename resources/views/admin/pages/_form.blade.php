{{--
    Gedeelde form-partial voor Pages create + edit.
    Verwacht: $page (Page-instance of null voor create), $action (URL), $method ('POST' of 'PUT').
--}}
@php
    $isEdit = ! is_null($page);

    // Vooringevulde waarden — old() wint, anders bestaande page-waarde, anders default
    $values = [
        'title' => old('title', $page?->title ?? ''),
        'slug' => old('slug', $page?->slug ?? ''),
        'excerpt' => old('excerpt', $page?->excerpt ?? ''),
        'body' => old('body', $page?->body ?? ''),
        'meta_title' => old('meta_title', $page?->meta_title ?? ''),
        'meta_description' => old('meta_description', $page?->meta_description ?? ''),
        'order' => old('order', $page?->order ?? 0),
    ];

    // Publicatie-toggle state
    $isPublished = old('is_published', $page?->published_at !== null);
    // Datum voor het datetime-local-veld (Y-m-d\TH:i)
    $publishedAt = old('published_at', $page?->published_at?->format('Y-m-d\TH:i') ?? '');
@endphp

<x-admin.form-layout :action="$action" :method="$method">
    {{-- ===== HOOFDCONTENT LINKS ===== --}}
    <x-slot:main>
        <x-admin.form-section title="Inhoud">
            <x-admin.field
                name="title"
                label="Titel"
                :value="$values['title']"
                required
                hint="Verschijnt als koptekst van de pagina en in de browser-tab."
            />

            <x-admin.field
                name="excerpt"
                label="Intro"
                type="textarea"
                :value="$values['excerpt']"
                hint="Korte intro. Wordt gebruikt als fallback voor meta-description."
            />

            <x-admin.tiptap-editor
                name="body"
                label="Tekst"
                :value="$values['body']"
                placeholder="Begin met schrijven…"
                required
            />
        </x-admin.form-section>

        <x-admin.form-section title="SEO">
            <x-admin.field
                name="meta_title"
                label="Meta-titel"
                :value="$values['meta_title']"
                hint="Optionele override. Maximaal 60 tekens werkt het best in Google."
            />

            <x-admin.field
                name="meta_description"
                label="Meta-beschrijving"
                type="textarea"
                :value="$values['meta_description']"
                hint="Optionele override. Maximaal 160 tekens werkt het best in Google."
            />
        </x-admin.form-section>
    </x-slot:main>

    {{-- ===== METADATA RECHTS ===== --}}
    <x-slot:side>
        <x-admin.form-section title="Publicatie">
            <div class="admin-field" x-data="{ published: {{ $isPublished ? 'true' : 'false' }} }">
                <label class="admin-field__label" for="is_published">
                    <input
                        type="checkbox"
                        name="is_published"
                        id="is_published"
                        value="1"
                        x-model="published"
                        style="margin-right: .5rem;"
                    >
                    Gepubliceerd
                </label>
                <p class="admin-field__hint">
                    Vink uit voor concept. Laat de datum leeg om direct te publiceren,
                    of kies een datum in de toekomst om in te plannen.
                </p>

                <div x-show="published" x-cloak style="margin-top: .75rem;">
                    <label class="admin-field__label" for="published_at">Publicatiedatum</label>
                    <input
                        type="datetime-local"
                        name="published_at"
                        id="published_at"
                        value="{{ $publishedAt }}"
                        class="form-control @error('published_at') is-invalid @enderror"
                    >
                    @error('published_at')
                        <p class="admin-field__error" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-admin.form-section>

        <x-admin.form-section title="URL">
            @if ($isEdit)
                {{-- Slug locked bij update — alleen-lezen, niet meegestuurd in form-data --}}
                <div class="admin-field">
                    <label class="admin-field__label" for="slug_display">Slug</label>
                    <input
                        type="text"
                        id="slug_display"
                        value="{{ $values['slug'] }}"
                        class="form-control"
                        readonly
                        disabled
                    >
                    <p class="admin-field__hint">
                        Slug is vergrendeld om dode links te voorkomen. Maak desgewenst een nieuwe pagina aan.
                    </p>
                </div>
            @else
                <x-admin.field
                    name="slug"
                    label="Slug"
                    :value="$values['slug']"
                    hint="Laat leeg om automatisch te genereren uit de titel. Alleen kleine letters, cijfers en streepjes."
                />
            @endif
        </x-admin.form-section>

        <x-admin.form-section title="Sortering">
            <x-admin.field
                name="order"
                label="Volgorde"
                type="number"
                :value="$values['order']"
                hint="Lager nummer = bovenaan. Standaard 0."
            />
        </x-admin.form-section>
    </x-slot:side>

    {{-- ===== ACTIE-KNOPPEN ===== --}}
    <x-slot:actions>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-link text-muted">
            Annuleren
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i>
            {{ $isEdit ? 'Wijzigingen opslaan' : 'Pagina aanmaken' }}
        </button>
    </x-slot:actions>
</x-admin.form-layout>
