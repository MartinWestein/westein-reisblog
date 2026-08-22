@extends('layouts.public')

@section('title', 'Reisroutes')
@section('meta_description', 'De reisroutes van familie Westein — onze reizen als geordende route langs de plekken die we bezochten, van roadtrips tot camperreizen.')

@section('content')
    <section class="routes-index" aria-labelledby="routes-index-title">
        <div class="container">
            <p class="section-label">Op stap</p>
            <h1 id="routes-index-title" class="section-title">Reisroutes</h1>
            <p class="routes-index__intro">
                {{-- TODO: intro-tekst verfijnen (analoog aan F5-22 homepage-hero placeholder) --}}
                Onze reizen als route: de plekken die we aandeden, in volgorde. Van meerdaagse
                roadtrips tot camperreizen met het gezin — blader door de routes en volg onze sporen.
            </p>

            @if ($routes->isNotEmpty())
                <div class="route-grid">
                    @foreach ($routes as $route)
                        <x-public.route-card :route="$route" />
                    @endforeach
                </div>
            @else
                <p class="routes-index__empty">Er zijn nog geen reisroutes gepubliceerd.</p>
            @endif
        </div>
    </section>
@endsection