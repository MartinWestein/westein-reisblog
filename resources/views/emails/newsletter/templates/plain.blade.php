@extends('emails.newsletter._layout')

@if ($newsletter->getFirstMediaUrl('header', 'medium'))
    @section('header')
        <img src="{{ $newsletter->getFirstMediaUrl('header', 'medium') }}" alt="" class="email-header-image" width="536">
    @endsection
@endif

@section('body')
    {!! $newsletter->body !!}
@endsection
