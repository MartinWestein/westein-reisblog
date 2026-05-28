@props(['member'])

@php $isEdit = $member->exists; @endphp

<x-admin.form-layout
    :action="$isEdit ? route('admin.family-members.update', $member) : route('admin.family-members.store')"
    :method="$isEdit ? 'PUT' : 'POST'"
    enctype="multipart/form-data"
>

    {{-- ============================================================
         LINKERKOLOM — hoofdcontent
         ============================================================ --}}
    <x-slot:main>
        <x-admin.form-section title="{{ __('Basisgegevens') }}">
            <x-admin.field
                name="name"
                label="{{ __('Naam') }}"
                :value="$member->name"
                required
                maxlength="120"
                autofocus
            />

            @if ($isEdit)
                <x-admin.field
                    name="slug_display"
                    label="{{ __('Slug') }}"
                    :value="$member->slug"
                    readonly
                    hint="🔒 {{ __('Vastgezet — slugs zijn niet aanpasbaar om SEO-links intact te houden.') }}"
                />
            @endif

            <x-admin.field
                name="role"
                label="{{ __('Rol') }}"
                :value="$member->role"
                maxlength="80"
                placeholder="{{ __('Bijv. Vader, Moeder, Reisplanner') }}"
                hint="{{ __('Vrije tekst — verschijnt onder de naam op de Over ons-pagina.') }}"
            />
        </x-admin.form-section>

        <x-admin.form-section title="{{ __('Biografie') }}"
                              description="{{ __('Korte beschrijving voor de Over ons-pagina.') }}">
            <x-admin.field
                name="bio"
                type="textarea"
                label="{{ __('Tekst') }}"
                :value="$member->bio"
                rows="8"
                maxlength="2000"
                placeholder="{{ __('Schrijf hier iets persoonlijks over deze persoon — interesses, rol in de familie, leukste reisherinnering…') }}"
                hint="{{ __('Max 2000 tekens. Witregels worden behouden.') }}"
            />
        </x-admin.form-section>
    </x-slot:main>

    {{-- ============================================================
         RECHTERKOLOM — metadata
         ============================================================ --}}
    <x-slot:side>
        <x-admin.form-section title="{{ __('Sortering') }}">
            <x-admin.field
                name="order"
                type="number"
                label="{{ __('Volgorde') }}"
                :value="$member->order ?? 0"
                hint="{{ __('Lager getal = eerder in de lijst.') }}"
                min="0"
                max="9999"
            />
        </x-admin.form-section>

        <x-admin.form-section title="{{ __('Gebruikersaccount') }}"
                              description="{{ __('Optioneel — koppel als dit familielid zelf kan inloggen.') }}">
            <div class="admin-field">
                <label for="field-user_id" class="admin-field__label">{{ __('Gekoppelde gebruiker') }}</label>

                <div class="admin-field__user-select">
                    <select
                        id="field-user_id"
                        name="user_id"
                        class="form-select @error('user_id') is-invalid @enderror"
                    >
                        <option value="">{{ __('— Niet gekoppeld —') }}</option>
                        @foreach ($users as $user)
                            <option
                                value="{{ $user->id }}"
                                @selected((int) old('user_id', $member->user_id) === $user->id)
                            >
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>

                    <button
                        type="button"
                        class="btn btn-outline-secondary btn-sm"
                        disabled
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ __('Beschikbaar in Stap 4.13 (Gebruikersbeheer)') }}"
                        aria-disabled="true"
                    >
                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    </button>
                </div>

                @error('user_id')
                    <span class="admin-field__error">{{ $message }}</span>
                @enderror
            </div>
        </x-admin.form-section>

        <x-admin.form-section title="{{ __('Portretfoto') }}">
            <x-admin.image-upload
                name="portrait"
                shape="circle"
                :current-url="$member->hasMedia('portrait') ? $member->getFirstMediaUrl('portrait', 'webp-300') : null"
                :max-mb="8"
                :min-width="300"
                :min-height="300"
                hint="{{ __('Vierkant werkt het mooist — andere ratios worden bijgesneden in de cirkel.') }}"
            />
        </x-admin.form-section>
    </x-slot:side>

    <x-slot:actions>
        <a href="{{ route('admin.family-members.index') }}" class="btn btn-link">
            {{ __('Annuleren') }}
        </a>
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? __('Wijzigingen opslaan') : __('Familielid toevoegen') }}
        </button>
    </x-slot:actions>
</x-admin.form-layout>
