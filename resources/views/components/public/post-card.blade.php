@props(['post'])

{{-- Herbruikbare post-kaart (F5-75). Gebruikt door home, /verhalen-index en
     (in 5.2.b) gerelateerde posts. URL via $post->url() (F5-71) — fixt de
     eerdere kapotte 2-segment-link. --}}
<article class="post-card">
    <a href="{{ $post->url() }}"
       class="post-card__link"
       aria-labelledby="post-card-title-{{ $post->id }}">

        @php
            $coverUrl = $post->getFirstMediaUrl('featured', 'medium')
                ?: $post->getFirstMediaUrl('featured');
        @endphp

        @if ($coverUrl)
            <img src="{{ $coverUrl }}"
                 alt="{{ $post->featured_image_alt ?: $post->title }}"
                 class="post-card__image"
                 loading="lazy">
        @else
            <div class="post-card__image-placeholder" aria-hidden="true">
                <i class="bi bi-image"></i>
            </div>
        @endif

        <div class="post-card__body">
            @if ($post->destination)
                <p class="post-card__meta">{{ $post->destination->name }}</p>
            @endif

            <h3 id="post-card-title-{{ $post->id }}" class="post-card__title">
                {{ $post->title }}
            </h3>

            @if ($post->excerpt)
                <p class="post-card__excerpt">{{ Str::limit($post->excerpt, 120) }}</p>
            @endif

            <p class="post-card__footer">
                <span>{{ optional($post->author)->name }}</span>
                <span aria-hidden="true">·</span>
                <time datetime="{{ $post->published_at->toIso8601String() }}">
                    {{ $post->published_at->translatedFormat('j F Y') }}
                </time>
            </p>
        </div>
    </a>
</article>