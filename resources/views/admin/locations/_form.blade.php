@php
    $isEdit = ! is_null($location);
@endphp

<x-admin.form-layout :action="$action" :method="$method">
    <x-slot:main>
        <x-admin.form-section title="{{ __('Algemeen') }}">
            <x-admin.field name="name" label="{{ __('Naam') }}"
                :value="$location?->name" required
                hint="{{ __('Bijv. Rome, Edinburgh of Cinque Terre.') }}" />

            @if ($isEdit)
                <x-admin.field name="slug" label="{{ __('Slug') }}"
                    :value="$location->slug" readonly
                    hint="{{ __('Slug ligt vast na aanmaken.') }}" />
            @else
                <x-admin.field name="slug" label="{{ __('Slug') }}"
                    hint="{{ __('Laat leeg om automatisch uit de naam te genereren.') }}" />
            @endif

            <x-admin.field name="description" label="{{ __('Beschrijving') }}" type="textarea"
                :value="$location?->description" rows="5" />
        </x-admin.form-section>

        <x-admin.form-section title="{{ __('Coördinaten') }}"
            description="{{ __('Voor straks: Leaflet-kaart op de publieke pagina.') }}">
            <div class="d-flex gap-3 flex-wrap">
                <div class="flex-fill" style="min-width: 200px;">
                    <x-admin.field name="latitude" label="{{ __('Latitude') }}" type="text"
                        :value="$location?->latitude"
                        hint="{{ __('Bijv. 41.9028. Tussen -90 en 90.') }}" />
                </div>
                <div class="flex-fill" style="min-width: 200px;">
                    <x-admin.field name="longitude" label="{{ __('Longitude') }}" type="text"
                        :value="$location?->longitude"
                        hint="{{ __('Bijv. 12.4964. Tussen -180 en 180.') }}" />
                </div>
            </div>
        </x-admin.form-section>
    </x-slot:main>

    <x-slot:side>
        <x-admin.form-section title="{{ __('Details') }}">
            <x-admin.field name="country_code" label="{{ __('Landcode') }}"
                :value="$location?->country_code"
                hint="{{ __('ISO 2-letter, bijv. IT of GB.') }}" maxlength="2" />
        </x-admin.form-section>
    </x-slot:side>

    <x-slot:actions>
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? __('Opslaan') : __('Aanmaken') }}
        </button>
        <a href="{{ route('admin.destinations.locations.index', $destination) }}" class="btn btn-link">
            {{ __('Annuleren') }}
        </a>
    </x-slot:actions>
</x-admin.form-layout>
