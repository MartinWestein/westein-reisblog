{{--
    Gedeelde form-partial voor Posts create + edit.
    Verwacht: $post (Post-instance of null), $action, $method ('POST'/'PUT'),
              $destinations, $locations, $categories, $allTags (uit PostController::formData()).
--}}
@php
    $isEdit = ! is_null($post);

    $values = [
        'title' => old('title', $post?->title ?? ''),
        'slug' => old('slug', $post?->slug ?? ''),
        'excerpt' => old('excerpt', $post?->excerpt ?? ''),
        'body' => old('body', $post?->body ?? ''),
        'featured_image_alt' => old('featured_image_alt', $post?->featured_image_alt ?? ''),
        'meta_title' => old('meta_title', $post?->meta_title ?? ''),
        'meta_description' => old('meta_description', $post?->meta_description ?? ''),
    ];

    // Status — default 'draft' bij nieuw
    $currentStatus = old('status', $post?->status ?? 'draft');

    // published_at voor datetime-local (Y-m-d\TH:i)
    $publishedAt = old('published_at', $post?->published_at?->format('Y-m-d\TH:i') ?? '');

    // Locatie/bestemming-keuze
    $selDestination = old('destination_id', $post?->destination_id ?? '');
    $selLocation = old('location_id', $post?->location_id ?? '');

    // Geselecteerde categorieën (array van id's)
    $selCategories = collect(old('categories', $isEdit ? $post->categories->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)->all();

    // Geselecteerde tags als komma-string voor de pills-factory
    $selTags = old('tags', $isEdit ? $post->tags->pluck('name')->implode(',') : '');
    // old('tags') kan een array zijn (na validatiefout) → terug naar string
    if (is_array($selTags)) {
        $selTags = implode(',', $selTags);
    }

    // Mag deze gebruiker publiceren? Bepaalt of published/scheduled in de dropdown staan.
    $canPublish = auth()->user()->can('posts.publish');

    // Featured image preview-url (edit)
    $featuredUrl = $isEdit ? $post->getFirstMediaUrl('featured', 'thumb') : '';
@endphp

<x-admin.form-layout :action="$action" :method="$method" enctype="multipart/form-data">
    {{-- ===== HOOFDCONTENT LINKS ===== --}}
    <x-slot:main>
        <x-admin.form-section title="Inhoud">
            <x-admin.field
                name="title"
                label="Titel"
                :value="$values['title']"
                required
                hint="De kop van het artikel en de browser-tab."
            />

            <x-admin.field
                name="excerpt"
                label="Intro"
                type="textarea"
                :value="$values['excerpt']"
                hint="Korte intro. Wordt gebruikt in overzichten en als fallback voor meta-description."
            />

            <x-admin.tiptap-editor-rich
                name="body"
                label="Inhoud"
                :value="$post?->body"
                :post="$post ?? null"
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
        {{-- Publicatie / status --}}
        <x-admin.form-section title="Publicatie">
            <div class="admin-field" x-data="{ status: '{{ $currentStatus }}' }">
                <label class="admin-field__label" for="status">Status</label>
                <select
                    name="status"
                    id="status"
                    x-model="status"
                    class="form-select @error('status') is-invalid @enderror"
                >
                    <option value="draft" @selected($currentStatus === 'draft')>Concept</option>
                    @if ($canPublish)
                        <option value="scheduled" @selected($currentStatus === 'scheduled')>Gepland</option>
                        <option value="published" @selected($currentStatus === 'published')>Gepubliceerd</option>
                    @endif
                    <option value="archived" @selected($currentStatus === 'archived')>Gearchiveerd</option>
                </select>
                @error('status')
                    <p class="admin-field__error" role="alert">{{ $message }}</p>
                @enderror

                @unless ($canPublish)
                    <p class="admin-field__hint">
                        Je kunt opslaan als concept. Publiceren of inplannen doet een editor.
                    </p>
                @endunless

                {{-- Datum alleen relevant bij 'scheduled' --}}
                <div x-show="status === 'scheduled'" x-cloak style="margin-top: .75rem;">
                    <label class="admin-field__label" for="published_at">Publicatiedatum</label>
                    <input
                        type="datetime-local"
                        name="published_at"
                        id="published_at"
                        value="{{ $publishedAt }}"
                        class="form-control @error('published_at') is-invalid @enderror"
                    >
                    <p class="admin-field__hint">Moet in de toekomst liggen.</p>
                    @error('published_at')
                        <p class="admin-field__error" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-admin.form-section>

        {{-- Bestemming + locatie (§3.4) --}}
        <x-admin.form-section title="Bestemming">
            <div
                class="admin-field"
                x-data="{ destination: '{{ $selDestination }}' }"
            >
                <label class="admin-field__label" for="destination_id">Bestemming</label>
                <select
                    name="destination_id"
                    id="destination_id"
                    x-model="destination"
                    class="form-select @error('destination_id') is-invalid @enderror"
                >
                    <option value="">— Geen (alleen toegestaan bij Tips) —</option>
                    @foreach ($destinations as $d)
                        <option value="{{ $d->id }}" @selected((string) $selDestination === (string) $d->id)>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
                @error('destination_id')
                    <p class="admin-field__error" role="alert">{{ $message }}</p>
                @enderror

                <div style="margin-top: .75rem;">
                    <label class="admin-field__label" for="location_id">Locatie (optioneel)</label>
                    <select
                        name="location_id"
                        id="location_id"
                        class="form-select @error('location_id') is-invalid @enderror"
                    >
                        <option value="">— Geen specifieke locatie —</option>
                        @foreach ($locations as $loc)
                            <option
                                value="{{ $loc->id }}"
                                data-destination="{{ $loc->destination_id }}"
                                x-show="destination === '' || destination === '{{ $loc->destination_id }}'"
                                @selected((string) $selLocation === (string) $loc->id)
                            >
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="admin-field__hint">Toont alleen locaties binnen de gekozen bestemming.</p>
                    @error('location_id')
                        <p class="admin-field__error" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-admin.form-section>

        {{-- Categorieën — eindige lijst, checkboxes --}}
        <x-admin.form-section title="Categorieën">
            <div class="admin-field @error('categories') is-invalid @enderror">
                @foreach ($categories as $cat)
                    <label class="admin-field__label d-flex align-items-center gap-2" style="font-weight: 400;">
                        <input
                            type="checkbox"
                            name="categories[]"
                            value="{{ $cat->id }}"
                            @checked(in_array($cat->id, $selCategories, true))
                        >
                        {{ $cat->name }}
                    </label>
                @endforeach
                @error('categories')
                    <p class="admin-field__error" role="alert">{{ $message }}</p>
                @enderror
                @error('categories.*')
                    <p class="admin-field__error" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </x-admin.form-section>

        {{-- Tags — pills met autocomplete --}}
        <x-admin.form-section title="Tags">
            <div
                class="admin-field tag-pills"
                x-data="tagPills(@js($selTags), @js($allTags))"
            >
                <div class="tag-pills__box" @click="$refs.input.focus()">
                    <template x-for="(tag, index) in tags" :key="index">
                        <span class="tag-pills__pill">
                            <span x-text="tag"></span>
                            <button type="button" class="tag-pills__remove" @click="removeTag(index)" aria-label="Verwijder tag">
                                <i class="bi bi-x"></i>
                            </button>
                        </span>
                    </template>

                    <input
                        type="text"
                        x-ref="input"
                        x-model="query"
                        class="tag-pills__input"
                        placeholder="Typ en kies of maak een tag…"
                        @focus="open = true"
                        @keydown.enter.prevent="onEnter()"
                        @keydown="if ($event.key === ',') { $event.preventDefault(); addTag(); }"
                        @keydown.backspace="onBackspace()"
                        @keydown.arrow-down.prevent="moveDown()"
                        @keydown.arrow-up.prevent="moveUp()"
                        @keydown.escape="open = false"
                    >
                </div>

                {{-- Autocomplete-dropdown --}}
                <div class="tag-pills__menu" x-show="open && suggestions.length > 0" x-cloak @click.outside="open = false">
                    <template x-for="(s, i) in suggestions" :key="s">
                        <button
                            type="button"
                            class="tag-pills__option"
                            :class="{ 'is-active': i === activeIndex }"
                            @click="addTag(s)"
                            @mouseenter="activeIndex = i"
                            x-text="s"
                        ></button>
                    </template>
                </div>

                <p class="admin-field__hint">Enter of komma om toe te voegen. Bestaande tags verschijnen als suggestie.</p>

                {{-- Bron-van-waarheid voor submit --}}
                <input type="hidden" name="tags" x-ref="hidden">
            </div>
        </x-admin.form-section>

        {{-- Featured image — bij create én edit --}}
        <x-admin.form-section title="Uitgelichte afbeelding">
            <x-admin.image-upload
                name="featured"
                shape="square"
                :current-url="$featuredUrl"
                :max-mb="8"
                :min-width="600"
                :min-height="400"
            />
            <x-admin.field
                name="featured_image_alt"
                label="Alt-tekst"
                :value="$values['featured_image_alt']"
                hint="Beschrijf de afbeelding voor schermlezers en SEO."
            />
        </x-admin.form-section>

        {{-- URL / slug --}}
        <x-admin.form-section title="URL">
            @if ($isEdit)
                <div class="admin-field">
                    <label class="admin-field__label" for="slug_display">Slug</label>
                    <input type="text" id="slug_display" value="{{ $values['slug'] }}" class="form-control" readonly disabled>
                    <p class="admin-field__hint">
                        Slug is vergrendeld om dode links te voorkomen.
                    </p>
                </div>
            @else
                <x-admin.field
                    name="slug"
                    label="Slug"
                    :value="$values['slug']"
                    hint="Laat leeg om automatisch te genereren uit de titel."
                />
            @endif
        </x-admin.form-section>
    </x-slot:side>

    {{-- ===== ACTIE-KNOPPEN ===== --}}
    <x-slot:actions>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-link text-muted">Annuleren</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i>
            {{ $isEdit ? 'Wijzigingen opslaan' : 'Post aanmaken' }}
        </button>
    </x-slot:actions>
</x-admin.form-layout>
