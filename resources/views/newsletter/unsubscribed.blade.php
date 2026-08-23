@extends('layouts.public')

@section('title', 'Uitschrijven')
@section('meta_description', 'Uitschrijven voor de nieuwsbrief van familie Westein.')

@section('content')
    <section class="static-page newsletter-result" aria-labelledby="newsletter-unsub-title">
        <div class="container">
            @if ($subscriber)
                <h1 id="newsletter-unsub-title" class="section-title static-page__title">Je bent uitgeschreven</h1>
                <div class="static-page__body">
                    <p>
                        Je ontvangt geen nieuwsbrief meer van ons. Jammer dat je gaat, maar we
                        begrijpen het — bedankt dat je een tijd hebt meegelezen.
                    </p>
                    <p>Van gedachten veranderd? Je kunt je op elk moment weer aanmelden.</p>
                    <p>
                        <a href="{{ route('newsletter.show') }}" class="btn btn-accent">Opnieuw aanmelden</a>
                    </p>
                </div>
            @else
                <h1 id="newsletter-unsub-title" class="section-title static-page__title">Deze uitschrijflink werkt niet</h1>
                <div class="static-page__body">
                    <p>Deze link is ongeldig. Mogelijk klopt er iets niet aan de link uit de e-mail.</p>
                    <p>
                        Wil je je uitschrijven maar lukt het niet? Neem gerust
                        <a href="{{ route('contact') }}">contact</a> met ons op, dan regelen we het.
                    </p>
                </div>
            @endif
        </div>
    </section>
@endsection
{{-- EOF --}}