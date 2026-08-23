@extends('layouts.public')

@section('title', $page->meta_title ?: $page->title)
@section('meta_description', Str::limit(strip_tags($page->meta_description ?: ($page->excerpt ?: '')), 160))

@section('content')
    <section class="static-page" aria-labelledby="static-page-title">
        <div class="container">
            <h1 id="static-page-title" class="section-title static-page__title">{{ $page->title }}</h1>

            <div class="static-page__body">
                {!! $page->body !!}
            </div>
        </div>
    </section>
@endsection
{{-- EOF --}}