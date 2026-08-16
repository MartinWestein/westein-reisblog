@extends('layouts.public')

{{-- 5.2.a: kale detail-view (F5-73). SEO-titel/meta-verfijning + hero + breadcrumb
     + gerelateerde posts + comments volgen in 5.2.b. --}}
@section('title', $post->title)
@section('meta_description', Str::limit(strip_tags($post->excerpt ?? ''), 160))

@section('content')
    <article class="post-detail">
        <div class="container">
            <header class="post-detail__header">
                <h1 class="post-detail__title">{{ $post->title }}</h1>

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
            </header>

            {{-- Body is bij opslag al door Purifier 'rich' gesaneerd (admin-controller).
                 Rendering-strategie (purify-at-output, prose-styling) is 5.2.b-materiaal (F5-73). --}}
            <div class="post-detail__body">
                {!! $post->body !!}
            </div>
        </div>
    </article>
@endsection