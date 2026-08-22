@extends('layouts.public')

@section('title', 'Reistips')
@section('meta_description', 'Praktische reistips van de familie Westein — pakken, vervoer, eten onderweg en de dingen die we op onze reizen leerden.')

@section('content')
    <section class="posts-index" aria-labelledby="reistips-index-title">
        <div class="container">
            <p class="section-label">Onze reistips</p>
            <h1 id="reistips-index-title" class="section-title">Reistips</h1>
            <p class="posts-index__intro">
                {{-- TODO: intro-tekst verfijnen (analoog aan F5-22 homepage-hero placeholder) --}}
                Praktische tips uit onze reizen, gebundeld op één plek — van pakken en vervoer
                tot eten onderweg met het gezin. De dingen die we zelf leerden, voor wie erna komt.
            </p>

            @if ($tips->isNotEmpty())
                <div class="post-grid">
                    @foreach ($tips as $tip)
                        <x-public.post-card :post="$tip" />
                    @endforeach
                </div>

                <div class="posts-index__pagination">
                    {{ $tips->links() }}
                </div>
            @else
                <p class="posts-index__empty">Er zijn nog geen reistips gepubliceerd.</p>
            @endif
        </div>
    </section>
@endsection