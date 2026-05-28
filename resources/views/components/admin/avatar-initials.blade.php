@props([
    'member',
    'size' => 120,
])

@php
    $hasPortrait = $member->hasMedia('portrait');
    $portraitUrl = $hasPortrait ? $member->getFirstMediaUrl('portrait', 'webp-300') : null;
    $fontSize = (int) ($size * 0.38);
@endphp

<div
    class="admin-avatar"
    style="width: {{ $size }}px; height: {{ $size }}px;"
>
    @if ($hasPortrait)
        <img
            src="{{ $portraitUrl }}"
            alt="{{ $member->name }}"
            class="admin-avatar__image"
            loading="lazy"
            width="{{ $size }}"
            height="{{ $size }}"
        >
    @else
        <span
            class="admin-avatar__initials"
            style="background-color: {{ $member->accentColor() }}; font-size: {{ $fontSize }}px;"
            aria-label="{{ $member->name }}"
        >
            {{ $member->initials() }}
        </span>
    @endif
</div>
