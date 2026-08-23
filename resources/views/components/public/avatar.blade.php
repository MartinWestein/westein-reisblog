@props([
    'subject',
    'size' => 96,
])
@php
    $portraitUrl = $subject->avatarUrl();
    $fontSize = (int) ($size * 0.4);
@endphp
<div class="author-avatar" style="width: {{ $size }}px; height: {{ $size }}px;">
    @if ($portraitUrl)
        <img src="{{ $portraitUrl }}"
             alt="{{ $subject->name }}"
             class="author-avatar__image"
             width="{{ $size }}"
             height="{{ $size }}"
             loading="lazy">
    @else
        <span class="author-avatar__initials"
              style="background-color: {{ $subject->accentColor() }}; font-size: {{ $fontSize }}px;"
              aria-label="{{ $subject->name }}">
            {{ $subject->initials() }}
        </span>
    @endif
</div>