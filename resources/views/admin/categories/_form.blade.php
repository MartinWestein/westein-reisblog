@props(['category' => null])

@php $isEdit = ! is_null($category); @endphp

<x-admin.field
    name="name"
    label="{{ __('Naam') }}"
    :value="$category?->name"
    required
    hint="{{ __('Bijvoorbeeld: Verslag, Tips, Eten, Activiteit.') }}"
/>

@if (! $isEdit)
    <x-admin.field
        name="slug"
        label="{{ __('Slug') }}"
        :value="old('slug')"
        hint="{{ __('Optioneel — laat leeg om automatisch uit naam af te leiden.') }}"
        x-data="{}"
        x-init="
            const nameField = document.getElementById('field-name');
            const slugField = $el;
            let touched = false;
            slugField.addEventListener('input', () => touched = true);
            nameField.addEventListener('input', () => {
                if (!touched) {
                    slugField.value = nameField.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
                }
            });
        "
    />
@else
    <x-admin.field
        name="slug_display"
        label="{{ __('Slug') }}"
        :value="$category->slug"
        readonly
        hint="🔒 {{ __('Vastgezet — slugs zijn niet aanpasbaar om SEO-links intact te houden.') }}"
    />
@endif

<x-admin.field
    name="description"
    type="textarea"
    label="{{ __('Beschrijving') }}"
    :value="$category?->description"
    rows="3"
    hint="{{ __('Korte uitleg (max 500 tekens) — gebruikt op de categorie-overzichtspagina.') }}"
/>

<x-admin.field
    name="order"
    type="number"
    label="{{ __('Volgorde') }}"
    :value="$category?->order ?? 0"
    hint="{{ __('Laag = bovenaan. Categorieën met gelijke volgorde worden alfabetisch gesorteerd.') }}"
/>
