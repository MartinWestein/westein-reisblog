@extends('layouts.public')

@section('title', 'Aanmelding bevestigen')
@section('meta_description', 'Bevestiging van je aanmelding voor de nieuwsbrief van familie Westein.')

@section('content')
    <section class="static-page newsletter-result" aria-labelledby="newsletter-result-title">
        <div class="container">
            @if ($subscriber)
                <h1 id="newsletter-result-title" class="section-title static-page__title">Je aanmelding is bevestigd</h1>
                <div class="static-page__body">
                    <p>
                        Bedankt{{ $subscriber->name ? ', '.$subscriber->name : '' }}! Je e-mailadres is
                        bevestigd en je staat nu op onze nieuwsbrieflijst. Je hoort binnenkort van ons.
                    </p>
                    <p>
                        <a href="{{ url('/') }}" class="btn btn-accent">Naar de homepage</a>
                    </p>
                </div>
            @else
                <h1 id="newsletter-result-title" class="section-title static-page__title">Deze bevestigingslink werkt niet</h1>
                <div class="static-page__body">
                    <p>
                        Deze link is ongeldig of is al gebruikt. Misschien had je je al bevestigd,
                        of is de link verlopen.
                    </p>
                    <p>
                        Klopt er iets niet? Meld je gerust opnieuw aan, dan sturen we je een nieuwe
                        bevestigingslink.
                    </p>
                    <p>
                        <a href="{{ route('newsletter.show') }}" class="btn btn-accent">Opnieuw aanmelden</a>
                    </p>
                </div>
            @endif
        </div>
    </section>
@endsection
{{-- EOF --}}