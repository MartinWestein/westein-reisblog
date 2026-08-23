@extends('layouts.public')

@section('title', $page?->meta_title ?: ($page?->title ?: 'Contact'))
@section('meta_description', Str::limit(strip_tags($page?->meta_description ?: ($page?->excerpt ?: '')), 160))

@section('content')
    <section class="static-page contact-page" aria-labelledby="contact-title">
        <div class="container">
            <h1 id="contact-title" class="section-title static-page__title">{{ $page?->title ?: 'Contact' }}</h1>

            @if ($page && $page->body)
                <div class="static-page__body">
                    {!! $page->body !!}
                </div>
            @endif

            @if (session('contact_success'))
                <div class="alert alert-success contact-form__success" role="status">
                    {{ session('contact_success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('contact.send') }}" class="contact-form">
                @csrf
                @honeypot

                <div class="contact-form__field">
                    <label for="contact-name" class="form-label">Naam</label>
                    <input type="text" id="contact-name" name="name" value="{{ old('name') }}"
                           maxlength="120" required
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>

                <div class="contact-form__field">
                    <label for="contact-email" class="form-label">E-mailadres</label>
                    <input type="email" id="contact-email" name="email" value="{{ old('email') }}"
                           maxlength="190" required
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>

                <div class="contact-form__field">
                    <label for="contact-subject" class="form-label">Onderwerp</label>
                    <input type="text" id="contact-subject" name="subject" value="{{ old('subject') }}"
                           maxlength="150" required
                           class="form-control @error('subject') is-invalid @enderror">
                    @error('subject') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>

                <div class="contact-form__field">
                    <label for="contact-message" class="form-label">Bericht</label>
                    <textarea id="contact-message" name="message" rows="6" maxlength="5000" required
                              class="form-control @error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                    @error('message') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-accent contact-form__submit">Bericht versturen</button>
            </form>
        </div>
    </section>
@endsection
{{-- EOF --}}