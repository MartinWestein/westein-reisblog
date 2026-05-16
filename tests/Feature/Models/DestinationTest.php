<?php

use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;
use App\Models\User;

test('destination genereert automatisch een slug uit name', function () {
    $destination = Destination::create([
        'name' => 'Toscane Italië',
        'description' => 'Mooie heuvels.',
    ]);

    expect($destination->slug)->toBe('toscane-italie');
});

test('destination-slug blijft stabiel na hernoemen', function () {
    $destination = Destination::create(['name' => 'Schotland']);
    $originalSlug = $destination->slug;

    $destination->update(['name' => 'Het Schotse Hoogland']);

    expect($destination->fresh()->slug)->toBe($originalSlug);
});

test('destination-slugs zijn uniek', function () {
    Destination::create(['name' => 'Italië']);
    $second = Destination::create(['name' => 'Italië']);

    expect($second->slug)->not->toBe('italie')
        ->and($second->slug)->toStartWith('italie-');
});

test('destination heeft veel locations', function () {
    $destination = Destination::factory()->create();
    Location::factory()->count(3)->for($destination)->create();

    expect($destination->locations)->toHaveCount(3)
        ->and($destination->locations->first())->toBeInstanceOf(Location::class);
});

test('destination heeft veel posts', function () {
    $user = User::factory()->create();
    $destination = Destination::factory()->create();

    Post::factory()->count(2)->for($user, 'author')->for($destination)->create([
        'location_id' => null,
    ]);

    expect($destination->posts)->toHaveCount(2);
});

test('soft-deleted destination blijft locations behouden', function () {
    $destination = Destination::factory()->create();
    Location::factory()->count(2)->for($destination)->create();

    $destination->delete(); // soft delete

    expect(Location::count())->toBe(2)
        ->and(Destination::withTrashed()->count())->toBe(1)
        ->and(Destination::count())->toBe(0);
});

test('herstellen van soft-deleted destination geeft locations terug', function () {
    $destination = Destination::factory()->create();
    Location::factory()->count(2)->for($destination)->create();

    $destination->delete();
    $destination->restore();

    expect(Destination::count())->toBe(1)
        ->and($destination->fresh()->locations)->toHaveCount(2);
});

test('locations worden verwijderd als destination wordt verwijderd', function () {
    $destination = Destination::factory()->create();
    Location::factory()->count(2)->for($destination)->create();

    $destination->forceDelete();

    expect(Location::count())->toBe(0);
});
