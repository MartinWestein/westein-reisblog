@extends('layouts.public')

@section('title', 'Verhalen')
@section('meta_description', 'Alle reisverhalen van de familie Westein — onze avonturen, ontdekkingen en belevenissen onderweg.')

@section('content')
    <section class="posts-index" aria-labelledby="posts-index-title">
        <div class="container">
            <p class="section-label">Onze verhalen</p>
            <h1 id="posts-index-title" class="section-title">Verhalen</h1>

            <p class="posts-index__intro">
                {{-- TODO: intro-tekst verfijnen (analoog aan F5-22 homepage-hero placeholder) --}}
                Alle verhalen van onze reizen op één plek. Van de eerste dag in een nieuwe stad tot de
                lessen die we onderweg leerden — blader terug door onze avonturen.
            </p>

            @if ($posts->isNotEmpty())
                <div class="post-grid">
                    @foreach ($posts as $post)
                        <x-public.post-card :post="$post" />
                    @endforeach
                </div>

                <div class="posts-index__pagination">
                    {{ $posts->links() }}
                </div>
            @else
                <p class="posts-index__empty">Er zijn nog geen verhalen gepubliceerd.</p>
            @endif
        </div>
    </section>
@endsection