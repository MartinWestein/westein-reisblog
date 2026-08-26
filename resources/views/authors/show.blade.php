@extends('layouts.public')

@section('title', $member->name)
@section('meta_description', Str::limit(strip_tags($member->bio ?: ''), 160))

@section('content')
    <x-public.json-ld :data="$member->personSchema()" />
    
    <x-public.breadcrumb :items="[
        ['label' => 'Over ons', 'url' => route('about')],
        ['label' => $member->name],
    ]" />

    <section class="author-detail" aria-labelledby="author-detail-title">
        <div class="container">
            <header class="author-detail__header">
                <x-public.avatar :subject="$member" :size="140" />
                <div class="author-detail__intro">
                    @if ($member->role)
                        <p class="section-label">{{ $member->role }}</p>
                    @endif
                    <h1 id="author-detail-title" class="section-title">{{ $member->name }}</h1>
                    @if ($member->bio)
                        <p class="author-detail__bio">{{ $member->bio }}</p>
                    @endif
                </div>
            </header>

            @if ($posts && $posts->isNotEmpty())
                <div class="author-detail__stories">
                    <h2 class="author-detail__stories-title">Verhalen van {{ $member->name }}</h2>
                    <div class="post-grid">
                        @foreach ($posts as $post)
                            <x-public.post-card :post="$post" />
                        @endforeach
                    </div>
                    <div class="author-detail__pagination">
                        {{ $posts->links() }}
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
{{-- EOF --}}