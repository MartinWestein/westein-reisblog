@extends('layouts.public')

@section('title', 'Nieuwsbrief')
@section('meta_description', 'Meld je aan voor de nieuwsbrief van familie Westein en ontvang onze nieuwste reisverhalen, tips en foto\'s in je inbox.')

@section('content')
    <section class="static-page newsletter-page" aria-labelledby="newsletter-title">
        <div class="container">
            <h1 id="newsletter-title" class="section-title static-page__title">Nieuwsbrief</h1>

            <div class="static-page__body newsletter-page__intro">
                <p>
                    Blijf op de hoogte van onze reizen. Meld je aan en we sturen je af en toe een
                    e-mail met onze nieuwste verhalen, reistips en foto's — niet vaker dan nodig,
                    en je kunt je op elk moment weer uitschrijven.
                </p>
            </div>

            @if (session('newsletter_success'))
                <div class="alert alert-success newsletter-form__success" role="status">
                    {{ session('newsletter_success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('newsletter.subscribe') }}" class="newsletter-form">
                @csrf
                @honeypot
                <div class="newsletter-form__field">
                    <label for="newsletter-name" class="form-label">
                        Naam <span class="newsletter-form__optional">(optioneel)</span>
                    </label>
                    <input type="text" id="newsletter-name" name="name" value="{{ old('name') }}"
                           maxlength="120"
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="newsletter-form__field">
                    <label for="newsletter-email" class="form-label">E-mailadres</label>
                    <input type="email" id="newsletter-email" name="email" value="{{ old('email') }}"
                           maxlength="190" required
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-accent newsletter-form__submit">Aanmelden</button>
            </form>
        </div>
    </section>
@endsection
{{-- EOF --}}