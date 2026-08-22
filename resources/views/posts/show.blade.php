@extends('layouts.public')

{{-- 5.2.b-i: post-detail afgemaakt — breadcrumb (F5-83), edge-to-edge hero
     (F5-82), SEO-meta via override-kolommen (F5-84), body-prose (F5-81),
     gerelateerde posts (F5-85). Comments volgen in 5.2.b-ii. --}}
@section('title', $post->meta_title ?: $post->title)
@section('meta_description', Str::limit(strip_tags($post->meta_description ?: $post->excerpt ?? ''), 160))

@section('content')
    @php
        $isTip = $post->categories->contains('slug', 'tips');
        $featured = $post->getFirstMedia('featured');
        $heroUrl = $featured?->getUrl('large') ?: $featured?->getUrl('medium') ?: $featured?->getUrl();
        $heroAlt = $post->featured_image_alt ?: $post->title;
    @endphp

    {{-- Breadcrumb: spiegelt de canonieke URL (F5-83). Tip roott in 'Reistips',
         nu een echte link naar de index (5.2.c). --}}
    @if ($isTip)
        <x-public.breadcrumb :items="[
            ['label' => 'Reistips', 'url' => route('reistips.index')],
            ['label' => $post->title],
        ]" />
    @else
        <x-public.breadcrumb :items="[
            ['label' => 'Bestemmingen', 'url' => route('destinations.index')],
            ['label' => $post->destination->name, 'url' => route('destinations.show', $post->destination)],
            ['label' => $post->location->name, 'url' => route('locations.show', [$post->destination, $post->location])],
            ['label' => $post->title],
        ]" />
    @endif

    <article class="post-detail">
        {{-- Edge-to-edge 2:1 hero uit de featured-collectie (F5-82) --}}
        <div class="post-detail__hero">
            @if ($heroUrl)
                <img src="{{ $heroUrl }}" alt="{{ $heroAlt }}" class="post-detail__hero-image">
            @else
                <div class="post-detail__hero-placeholder" aria-hidden="true">
                    <i class="bi bi-image"></i>
                </div>
            @endif
        </div>

        {{-- Kop: eyebrow-label + titel + excerpt + meta --}}
        <header class="post-detail__intro">
            <div class="container">
                <p class="section-label">{{ $isTip ? 'Reistip' : $post->destination->name }}</p>
                <h1 class="post-detail__title section-title">{{ $post->title }}</h1>
                @if ($post->excerpt)
                    <p class="post-detail__excerpt">{{ $post->excerpt }}</p>
                @endif
                <p class="post-detail__meta">
                    <span>{{ optional($post->author)->name }}</span>
                    <span aria-hidden="true">·</span>
                    <time datetime="{{ $post->published_at->toIso8601String() }}">
                        {{ $post->published_at->translatedFormat('j F Y') }}
                    </time>
                </p>
            </div>
        </header>

        {{-- Body: bij opslag al Purifier-'rich'-gesaneerd (F5-81, purify-at-save) --}}
        <div class="post-detail__body-wrap">
            <div class="container">
                <div class="post-detail__body">
                    {!! $post->body !!}
                </div>
            </div>
        </div>
    </article>

    {{-- Reacties (5.2.b-ii): threaded 1 niveau (F5-86), oudste eerst (F5-89),
         eigen pending zichtbaar (F5-87), inlog-oproep voor gasten (F5-88). --}}
    <section id="reacties" class="post-comments" aria-labelledby="post-comments-title">
        <div class="container">
            <p class="section-label">Reacties</p>
            <h2 id="post-comments-title" class="section-title">
                {{ $commentsCount }} {{ $commentsCount === 1 ? 'reactie' : 'reacties' }}
            </h2>

            @if (session('comment_success'))
                <div class="post-comments__flash" role="status">
                    <i class="bi bi-check-circle me-1" aria-hidden="true"></i>{{ session('comment_success') }}
                </div>
            @endif

            @forelse ($comments as $comment)
                <article id="reactie-{{ $comment->id }}" class="comment">
                    <x-public.comment :comment="$comment">
                        @auth
                            <div class="comment__actions" x-data="{ replying: false }">
                                <button type="button" class="comment__reply-toggle"
                                        @click="replying = !replying"
                                        x-text="replying ? 'Annuleren' : 'Reageren'">Reageren</button>
                                <div class="comment__reply-form" x-show="replying" x-cloak>
                                    <x-public.comment-form :post="$post" :parent="$comment" />
                                </div>
                            </div>
                        @endauth
                    </x-public.comment>

                    @if ($comment->replies->isNotEmpty())
                        <div class="comment__replies">
                            @foreach ($comment->replies as $reply)
                                <div id="reactie-{{ $reply->id }}">
                                    <x-public.comment :comment="$reply" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            @empty
                <p class="post-comments__empty">Er zijn nog geen reacties. Wees de eerste!</p>
            @endforelse

            @auth
                <div class="post-comments__form">
                    <h3 class="post-comments__form-title">Laat een reactie achter</h3>
                    <x-public.comment-form :post="$post" />
                </div>
            @else
                <div class="post-comments__login">
                    <p class="mb-0">Wil je reageren? <a href="{{ route('login') }}">Log in</a> of <a href="{{ route('register') }}">maak een account</a> aan.</p>
                </div>
            @endauth
        </div>
    </section>
        
    {{-- Gerelateerde posts (F5-85): verborgen bij 0 --}}
    @if ($related->isNotEmpty())
        <section class="post-detail__related" aria-labelledby="post-detail-related-title">
            <div class="container">
                <p class="section-label">Meer lezen</p>
                <h2 id="post-detail-related-title" class="section-title">
                    {{ $isTip ? 'Andere reistips' : 'Meer uit deze reis' }}
                </h2>
                <div class="post-grid">
                    @foreach ($related as $relatedPost)
                        <x-public.post-card :post="$relatedPost" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
{{-- EINDE posts/show.blade.php — veilig om te kopiëren --}}