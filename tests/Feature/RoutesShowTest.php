<?php

use App\Models\Destination;
use App\Models\Location;
use App\Models\Route;

use function Pest\Laravel\get;

it('toont de route-detailpagina voor gasten', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    Route::factory()->for($destination)->create([
        'name' => 'Italie roadtrip',
        'slug' => 'italie-roadtrip',
        'description' => 'Drie weken door Toscane, Lazio en Veneto.',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    get('/reisroutes/italie-roadtrip')
        ->assertOk()
        ->assertSee('Italie roadtrip')
        ->assertSee('Drie weken door Toscane, Lazio en Veneto.');
});

it('geeft 404 voor een niet-gepubliceerde route', function () {
    $destination = Destination::factory()->create(['slug' => 'schotland', 'name' => 'Schotland']);
    Route::factory()->for($destination)->create([
        'slug' => 'conceptroute',
        'name' => 'Conceptroute',
        'is_published' => false,
        'published_at' => null,
    ]);

    get('/reisroutes/conceptroute')->assertNotFound();
});

it('geeft 404 voor een onbekende route-slug', function () {
    get('/reisroutes/bestaat-niet')->assertNotFound();
});

it('toont de waypoint-locaties in volgorde', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $rome = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $florence = Location::factory()->for($destination)->create(['slug' => 'florence', 'name' => 'Florence']);

    $route = Route::factory()->for($destination)->create([
        'slug' => 'italie-roadtrip',
        'name' => 'Italie roadtrip',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    $route->locations()->attach($rome->id, ['order' => 0]);
    $route->locations()->attach($florence->id, ['order' => 1]);

    get('/reisroutes/italie-roadtrip')
        ->assertOk()
        ->assertSeeInOrder(['Rome', 'Florence']);
});

it('toont een breadcrumb met een link naar de reisroutes-index', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    Route::factory()->for($destination)->create([
        'slug' => 'kruimelroute',
        'name' => 'Kruimelroute',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $response = get('/reisroutes/kruimelroute')->assertOk();
    $response->assertSee('aria-label="Kruimelspoor"', false);
    $response->assertSee(route('reisroutes.index'), false);
    $response->assertSee('Kruimelroute');
});

it('rendert de terug-CTA naar de reisroutes-index', function () {
    $destination = Destination::factory()->create(['slug' => 'ergens', 'name' => 'Ergens']);
    Route::factory()->for($destination)->create([
        'slug' => 'eenroute',
        'name' => 'Eenroute',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    get('/reisroutes/eenroute')
        ->assertOk()
        ->assertSee('Alle reisroutes')
        ->assertSee(route('reisroutes.index'), false);
});

it('toont de routenaam in de paginatitel', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    Route::factory()->for($destination)->create([
        'slug' => 'titelroute',
        'name' => 'Titelroute',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    get('/reisroutes/titelroute')
        ->assertOk()
        ->assertSee('<title>Titelroute — '.config('app.name').'</title>', false);
});
