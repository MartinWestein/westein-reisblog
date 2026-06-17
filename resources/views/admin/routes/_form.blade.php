@php
    $isEdit = isset($route) && $route?->exists;

    $waypointsForJs = $isEdit
        ? $route->waypoints->map(fn ($w) => [
            '_uid' => 'srv-'.$w->id,
            'location_id' => (int) $w->location_id,
            'notes' => $w->notes ?? '',
        ])->values()->all()
        : [];

    $locationsForJs = $locations->map(fn ($l) => [
        'id' => (int) $l->id,
        'destination_id' => (int) $l->destination_id,
        'name' => $l->name,
        'latitude' => $l->latitude !== null ? (float) $l->latitude : null,
        'longitude' => $l->longitude !== null ? (float) $l->longitude : null,
    ])->values()->all();

    $initialDestinationId = old('destination_id', $route?->destination_id);
@endphp

<div
    x-data="routeWaypoints({
        initialWaypoints: @js($waypointsForJs),
        locations: @js($locationsForJs),
        initialDestinationId: @js($initialDestinationId),
    })">

    <x-admin.form-layout :action="$action" :method="$method" enctype="multipart/form-data">
        <x-slot:main>
            <x-admin.form-section title="Algemeen">
                <div class="admin-field">
                    <label for="field-destination_id" class="admin-field__label">
                        {{ __('Bestemming') }} <span class="admin-field__required">*</span>
                    </label>
                    <select
                        id="field-destination_id"
                        name="destination_id"
                        required
                        x-model="destinationId"
                        class="form-select @error('destination_id') is-invalid @enderror"
                    >
                        <option value="">{{ __('— Kies een bestemming —') }}</option>
                        @foreach ($destinations as $d)
                            <option value="{{ $d->id }}" @selected((string) $initialDestinationId === (string) $d->id)>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('destination_id')
                        <span class="admin-field__error">{{ $message }}</span>
                    @enderror
                </div>

                <x-admin.field
                    name="name"
                    :label="__('Naam')"
                    :value="$route?->name"
                    :required="true"
                    placeholder="bv. Italiaanse roadtrip 2024"
                />

                @if (! $isEdit)
                    <x-admin.field
                        name="slug"
                        :label="__('URL-slug')"
                        :hint="__('Optioneel. Wordt automatisch gegenereerd uit de naam.')"
                    />
                @else
                    <div class="admin-field">
                        <label class="admin-field__label">{{ __('URL-slug') }}</label>
                        <input type="text" value="{{ $route->slug }}" readonly class="form-control">
                        <small class="admin-field__hint">{{ __('Slug staat vast na aanmaken.') }}</small>
                    </div>
                @endif

                <x-admin.tiptap-editor
                    name="description"
                    :label="__('Beschrijving')"
                    :value="$route?->description"
                    :hint="__('Optioneel verhaal of toelichting bij deze reis.')"
                />
            </x-admin.form-section>

            <x-admin.form-section
                title="Waypoints"
                description="Voeg locaties toe in reisvolgorde. Sleep aan het handvat om te ordenen."
            >
                <input type="hidden" name="waypoints" x-ref="hidden">
                @error('waypoints')
                    <p class="admin-field__error">{{ $message }}</p>
                @enderror

                <template x-if="waypoints.length === 0">
                    <p class="waypoint-list-empty">
                        <span x-show="!destinationId">{{ __('Kies eerst een bestemming hierboven.') }}</span>
                        <span x-show="destinationId">{{ __('Nog geen waypoints. Kies hieronder een locatie en voeg toe.') }}</span>
                    </p>
                </template>

                <div data-waypoint-list>
                    <template x-for="(wp, idx) in waypoints" :key="wp._uid">
                        <div class="waypoint-row">
                            <button
                                type="button"
                                class="waypoint-row__handle"
                                title="{{ __('Sleep om volgorde te wijzigen') }}"
                                aria-label="{{ __('Versleep waypoint') }}"
                            >
                                <i class="bi bi-grip-vertical"></i>
                            </button>

                            <div class="waypoint-row__main">
                                <div class="waypoint-row__location">
                                    <span x-text="(idx + 1) + '. ' + locationName(wp.location_id)"></span>
                                    <template x-if="isWaypointOutsideDestination(idx)">
                                        <span class="waypoint-row__warn">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            {{ __('buiten de gekozen bestemming') }}
                                        </span>
                                    </template>
                                </div>
                                <textarea
                                    class="form-control form-control-sm waypoint-row__notes"
                                    rows="2"
                                    placeholder="{{ __('Optionele notitie, bv. Dag 3: overnacht in Florence') }}"
                                    :value="wp.notes"
                                    @input="updateNotes(idx, $event.target.value)"
                                ></textarea>
                            </div>

                            <button
                                type="button"
                                class="waypoint-row__remove"
                                @click="removeWaypoint(idx)"
                                title="{{ __('Verwijder dit waypoint') }}"
                                aria-label="{{ __('Verwijder waypoint') }}"
                            >
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="waypoint-add" x-show="destinationId" x-cloak>
                    <select x-model="selectedLocationId" class="form-select">
                        <option value="">{{ __('— Kies een locatie —') }}</option>
                        <template x-for="loc in availableLocations" :key="loc.id">
                            <option :value="loc.id" x-text="loc.name"></option>
                        </template>
                    </select>
                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        @click="addWaypoint()"
                        :disabled="!selectedLocationId"
                    >
                        <i class="bi bi-plus-lg"></i> {{ __('Voeg waypoint toe') }}
                    </button>
                </div>
            </x-admin.form-section>
        </x-slot:main>

        <x-slot:side>
            <x-admin.form-section title="Publicatie">
                <div
                    x-data="{
                        isPublished: @js((bool) old('is_published', $route?->is_published)),
                        publishedAt: @js(old('published_at', $route?->published_at?->format('Y-m-d\TH:i')) ?? ''),
                    }"
                >
                    <label class="d-flex align-items-center gap-2">
                        <input
                            type="checkbox"
                            name="is_published"
                            value="1"
                            x-model="isPublished"
                        >
                        <span>{{ __('Publiek zichtbaar') }}</span>
                    </label>
                    <small class="admin-field__hint d-block mt-1">
                        {{ __('Concept: alleen jij ziet de route. Gepubliceerd: openbaar (tenzij toekomstige datum).') }}
                    </small>

                    <div class="mt-3" x-show="isPublished" x-cloak>
                        <label for="field-published_at" class="admin-field__label">
                            {{ __('Publicatiedatum') }}
                        </label>
                        <input
                            type="datetime-local"
                            id="field-published_at"
                            name="published_at"
                            x-model="publishedAt"
                            class="form-control @error('published_at') is-invalid @enderror"
                        >
                        <small class="admin-field__hint">
                            {{ __('Laat leeg voor "nu". Toekomstige datum = gepland.') }}
                        </small>
                        @error('published_at')
                            <span class="admin-field__error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </x-admin.form-section>

            <x-admin.form-section title="Reis">
                <x-admin.field
                    name="travel_date"
                    :label="__('Reisdatum')"
                    type="date"
                    :value="$route?->travel_date?->format('Y-m-d')"
                    :hint="__('Wanneer vond deze reis plaats?')"
                />
            </x-admin.form-section>

            <x-admin.form-section title="Hero-afbeelding">
                <x-admin.image-upload
                    name="hero"
                    :current-url="$isEdit ? ($route->getFirstMediaUrl('hero', 'webp-400') ?: null) : null"
                    :min-width="600"
                    :min-height="338"
                    :max-mb="16"
                    :hint="__('Optioneel. Zonder eigen hero pakken we de eerste foto van het eerste waypoint.')"
                />
            </x-admin.form-section>

            <x-admin.form-section title="Preview">
                <button
                    type="button"
                    class="btn btn-outline-secondary w-100"
                    @click="openMapPreview()"
                    :disabled="waypoints.length === 0"
                >
                    <i class="bi bi-map"></i> {{ __('Preview op kaart') }}
                </button>
                <small class="admin-field__hint d-block mt-2">
                    <span x-show="waypoints.length === 0">{{ __('Voeg eerst waypoints toe.') }}</span>
                    <span x-show="waypoints.length > 0" x-cloak>
                        {{ __('Toont alle waypoints in volgorde met verbindingslijn.') }}
                    </span>
                </small>
            </x-admin.form-section>
        </x-slot:side>

        <x-slot:actions>
            <a href="{{ route('admin.reisroutes.index') }}" class="btn btn-link">{{ __('Annuleren') }}</a>
            <button type="submit" class="btn btn-primary">
                {{ $isEdit ? __('Reisroute bijwerken') : __('Reisroute aanmaken') }}
            </button>
        </x-slot:actions>
    </x-admin.form-layout>

    <div
        class="modal fade"
        id="routeMapPreviewModal"
        tabindex="-1"
        aria-labelledby="routeMapPreviewModalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="routeMapPreviewModalLabel">
                        {{ __('Routevoorbeeld op kaart') }}
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="{{ __('Sluiten') }}"
                    ></button>
                </div>
                <div class="modal-body">
                    <div id="routeMapPreviewCanvas" style="height: 500px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>  {{-- sluit van het x-data="routeWaypoints" block --}}
