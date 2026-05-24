@props(['tag' => null])

@php $isEdit = ! is_null($tag); @endphp

<x-admin.field
    name="name"
    label="{{ __('Naam') }}"
    :value="$tag?->name"
    required
    hint="{{ __('Wordt automatisch lowercase. Bijvoorbeeld: camper, italie, kinderen.') }}"
/>

@if ($isEdit)
    <x-admin.field
        name="slug_display"
        label="{{ __('Slug') }}"
        :value="$tag->slug"
        readonly
        hint="🔒 {{ __('Vastgezet — wordt afgeleid van de naam bij aanmaak en blijft stabiel voor SEO-links.') }}"
    />
@endif
