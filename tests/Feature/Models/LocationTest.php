<?php

use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;
use App\Models\User;

test('location genereert automatisch een slug uit name', function () {
    $destination = Destination::factory()->create();

    $location = Location::create([
        'destination_id' => $destination->id,
        'name' => 'Rome',
    ]);

    expect($location->slug)->toBe('rome');
});

test('location-slugs zijn globaal uniek (over destinations heen)', function () {
    $destinationA = Destination::factory()->create();
    $destinationB = Destination::factory()->create();

    Location::create(['destination_id' => $destinationA->id, 'name' => 'Florence']);
    $second = Location::create(['destination_id' => $destinationB->id, 'name' => 'Florence']);

    expect($second->slug)->not->toBe('florence')
        ->and($second->slug)->toStartWith('florence-');
});

test('location behoort tot een destination', function () {
    $destination = Destination::factory()->create();
    $location = Location::factory()->for($destination)->create();

    expect($location->destination)->toBeInstanceOf(Destination::class)
        ->and($location->destination->id)->toBe($destination->id);
});

test('location heeft veel posts', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();

    Post::factory()->count(3)->for($user)->create([
        'location_id' => $location->id,
        'destination_id' => $location->destination_id,
    ]);

    expect($location->posts)->toHaveCount(3);
});

test('location-coordinaten worden als decimal gecast', function () {
    $location = Location::factory()->create([
        'latitude' => 41.9028000,
        'longitude' => 12.4964000,
    ]);

    expect((string) $location->latitude)->toBe('41.9028000')
        ->and((string) $location->longitude)->toBe('12.4964000');
});
