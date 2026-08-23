@extends('layouts.public')

@section('title', $page?->meta_title ?: ($page?->title ?: 'Over ons'))
@section('meta_description', Str::limit(strip_tags($page?->meta_description ?: ($page?->excerpt ?: '')), 160))

@section('content')
    <section class="over-ons" aria-labelledby="over-ons-title">
        <div class="container">
            <p class="section-label">Wie we zijn</p>
            <h1 id="over-ons-title" class="section-title">{{ $page?->title ?: 'Over ons' }}</h1>

            @if ($page && $page->body)
                <div class="over-ons__intro">
                    {!! $page->body !!}
                </div>
            @endif

            @if ($members->isNotEmpty())
                <div class="authors-grid">
                    @foreach ($members as $member)
                        <article class="author-card" aria-labelledby="author-card-title-{{ $member->id }}">
                            <a href="{{ route('authors.show', $member) }}" class="author-card__link">
                                <x-public.avatar :subject="$member" :size="120" />
                                <div class="author-card__body">
                                    <h2 id="author-card-title-{{ $member->id }}" class="author-card__name">{{ $member->name }}</h2>
                                    @if ($member->role)
                                        <p class="author-card__role">{{ $member->role }}</p>
                                    @endif
                                    @if ($member->bio)
                                        <p class="author-card__bio">{{ Str::limit($member->bio, 120) }}</p>
                                    @endif
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
{{-- EOF --}}