<?php

use App\Models\Destination;
use App\Models\Route;

use function Pest\Laravel\get;

it('toont de reisroutes-index met gepubliceerde routes', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    Route::factory()->for($destination)->create([
        'name' => 'Italie roadtrip 2024',
        'slug' => 'italie-roadtrip-2024',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    get('/reisroutes')
        ->assertOk()
        ->assertSee('Reisroutes')
        ->assertSee('Italie roadtrip 2024');
});

it('toont geen niet-gepubliceerde routes op de index', function () {
    $destination = Destination::factory()->create(['slug' => 'schotland', 'name' => 'Schotland']);
    Route::factory()->for($destination)->create([
        'name' => 'Verborgen conceptroute',
        'slug' => 'verborgen-conceptroute',
        'is_published' => false,
        'published_at' => null,
    ]);

    get('/reisroutes')
        ->assertOk()
        ->assertDontSee('Verborgen conceptroute');
});

it('toont de uitgelicht-badge bij een featured route', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    Route::factory()->for($destination)->create([
        'name' => 'Uitgelichte route',
        'slug' => 'uitgelichte-route',
        'is_published' => true,
        'published_at' => now()->subDay(),
        'is_featured' => true,
    ]);

    get('/reisroutes')
        ->assertOk()
        ->assertSee('Uitgelicht');
});

it('zet featured routes vooraan, ongeacht reisdatum', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);

    // Niet-featured route met een NIEUWERE reisdatum.
    Route::factory()->for($destination)->create([
        'name' => 'Nieuwe gewone route',
        'slug' => 'nieuwe-gewone-route',
        'is_published' => true,
        'published_at' => now()->subDay(),
        'is_featured' => false,
        'travel_date' => '2025-01-01',
    ]);

    // Featured route met een OUDERE reisdatum -> hoort toch bovenaan.
    Route::factory()->for($destination)->create([
        'name' => 'Oude uitgelichte route',
        'slug' => 'oude-uitgelichte-route',
        'is_published' => true,
        'published_at' => now()->subDay(),
        'is_featured' => true,
        'travel_date' => '2019-01-01',
    ]);

    get('/reisroutes')
        ->assertOk()
        ->assertSeeInOrder(['Oude uitgelichte route', 'Nieuwe gewone route']);
});
