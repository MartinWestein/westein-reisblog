@props(['comment'])

{{-- Eén reactie: avatar-initialen + meta + tekst. $slot voor de reply-toggle/-form
     (alleen bij top-level meegegeven). Body is platte tekst -> e() + nl2br. --}}
@php
    $name = $comment->author?->name ?? 'Onbekend';
    $initials = collect(explode(' ', trim($name)))
        ->filter()
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->take(2)
        ->implode('');
@endphp
<div class="comment__row">
    <div class="comment__avatar" aria-hidden="true">{{ $initials ?: '?' }}</div>
    <div class="comment__content">
        <p class="comment__meta">
            <span class="comment__author">{{ $name }}</span>
            <span class="comment__dot" aria-hidden="true">·</span>
            <time datetime="{{ $comment->created_at->toIso8601String() }}">
                {{ $comment->created_at->translatedFormat('j F Y') }}
            </time>
            @if ($comment->status === 'pending')
                <span class="comment__pending" title="Alleen voor jou zichtbaar tot een beheerder de reactie goedkeurt">
                    <i class="bi bi-hourglass-split" aria-hidden="true"></i> wacht op goedkeuring
                </span>
            @endif
        </p>
        <div class="comment__text">{!! nl2br(e($comment->body)) !!}</div>
        {{ $slot }}
    </div>
</div>