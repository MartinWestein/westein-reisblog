<?php

use App\Models\Tag;

test('naam wordt lowercase opgeslagen', function () {
    $tag = Tag::create(['name' => 'Camper']);

    expect($tag->fresh()->name)->toBe('camper');
});

test('naam wordt getrimd', function () {
    $tag = Tag::create(['name' => '  Roadtrip  ']);

    expect($tag->fresh()->name)->toBe('roadtrip');
});

test('slug wordt automatisch gegenereerd', function () {
    $tag = Tag::create(['name' => 'reizen met kinderen']);

    expect($tag->slug)->toBe('reizen-met-kinderen');
});

test('route key is slug', function () {
    $tag = Tag::create(['name' => 'kamperen']);

    expect($tag->getRouteKeyName())->toBe('slug');
});
