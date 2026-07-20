@php
    $isEdit = ! is_null($destination);
@endphp

<x-admin.form-layout :action="$action" :method="$method" enctype="multipart/form-data">
    <x-slot:main>
        <x-admin.form-section title="{{ __('Algemeen') }}">
            <x-admin.field name="name" label="{{ __('Naam') }}"
                :value="$destination?->name" required
                hint="{{ __('Bijv. Italië of Schotland.') }}" />

            @if ($isEdit)
                <x-admin.field name="slug" label="{{ __('Slug') }}"
                    :value="$destination->slug" readonly
                    hint="{{ __('Slug ligt vast na aanmaken.') }}" />
            @else
                <x-admin.field name="slug" label="{{ __('Slug') }}"
                    hint="{{ __('Laat leeg om automatisch uit de naam te genereren.') }}" />
            @endif

            <x-admin.field name="description" label="{{ __('Beschrijving') }}" type="textarea"
                :value="$destination?->description" rows="5" />
        </x-admin.form-section>
    </x-slot:main>

    <x-slot:side>
        <x-admin.form-section title="{{ __('Details') }}">
            <x-admin.field name="country_code" label="{{ __('Landcode') }}"
                :value="$destination?->country_code"
                hint="{{ __('ISO 2-letter, bijv. IT of GB.') }}" maxlength="2" />

            <div class="form-check mt-3">
                <input type="checkbox" class="form-check-input" id="is_featured"
                       name="is_featured" value="1"
                       @checked(old('is_featured', $destination?->is_featured))>
                <label class="form-check-label" for="is_featured">
                    {{ __('Uitgelicht op de homepage en index') }}
                </label>
            </div>
        </x-admin.form-section>

        <x-admin.form-section title="{{ __('Hero-afbeelding') }}">
            @php
                $hero = $destination?->getFirstMedia('hero');
                $heroUrl = $hero?->hasGeneratedConversion('medium') ? $hero->getUrl('medium') : $hero?->getUrl();
            @endphp
            <x-admin.image-upload name="hero" shape="square" :current-url="$heroUrl" />
        </x-admin.form-section>
    </x-slot:side>

    <x-slot:actions>
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? __('Opslaan') : __('Aanmaken') }}
        </button>
        <a href="{{ route('admin.destinations.index') }}" class="btn btn-link">{{ __('Annuleren') }}</a>
    </x-slot:actions>
</x-admin.form-layout>
