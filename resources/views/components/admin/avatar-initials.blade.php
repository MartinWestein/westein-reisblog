@props([
    'subject',
    'size' => 120,
])
@php
    $portraitUrl = $subject->avatarUrl();
    $fontSize = (int) ($size * 0.38);
@endphp
<div
    class="admin-avatar"
    style="width: {{ $size }}px; height: {{ $size }}px;"
>
    @if ($portraitUrl)
        <img
            src="{{ $portraitUrl }}"
            alt="{{ $subject->name }}"
            class="admin-avatar__image"
            loading="lazy"
            width="{{ $size }}"
            height="{{ $size }}"
        >
    @else
        <span
            class="admin-avatar__initials"
            style="background-color: {{ $subject->accentColor() }}; font-size: {{ $fontSize }}px;"
            aria-label="{{ $subject->name }}"
        >
            {{ $subject->initials() }}
        </span>
    @endif
</div>
