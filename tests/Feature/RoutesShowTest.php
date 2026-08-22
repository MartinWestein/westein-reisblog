<?php

use App\Models\Category;
use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;
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

it('toont de kaart-container met waypoint-coordinaten', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $rome = Location::factory()->for($destination)->create([
        'slug' => 'rome',
        'name' => 'Rome',
        'latitude' => 41.9028,
        'longitude' => 12.4964,
    ]);
    $florence = Location::factory()->for($destination)->create([
        'slug' => 'florence',
        'name' => 'Florence',
        'latitude' => 43.7696,
        'longitude' => 11.2558,
    ]);

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
        ->assertSee('data-route-map', false)
        ->assertSee('41.9028')
        ->assertSee('12.4964');
});

it('verbergt de kaart als de route geen waypoints heeft', function () {
    $destination = Destination::factory()->create(['slug' => 'leeg', 'name' => 'Leeg']);
    Route::factory()->for($destination)->create([
        'slug' => 'lege-route',
        'name' => 'Lege route',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    get('/reisroutes/lege-route')
        ->assertOk()
        ->assertDontSee('data-route-map', false);
});

it('linkt elke waypoint naar zijn location-detail en toont de notes', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $rome = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);

    $route = Route::factory()->for($destination)->create([
        'slug' => 'italie-roadtrip',
        'name' => 'Italie roadtrip',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    $route->locations()->attach($rome->id, ['order' => 0, 'notes' => 'Start in Rome']);

    get('/reisroutes/italie-roadtrip')
        ->assertOk()
        ->assertSee(route('locations.show', [$destination, $rome]), false)
        ->assertSee('Start in Rome');
});

it('toont verhalen van dezelfde reis, exclusief tips en andere bestemmingen', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $tips = Category::factory()->create(['name' => 'Tips']);

    Route::factory()->for($destination)->create([
        'slug' => 'italie-roadtrip',
        'name' => 'Italie roadtrip',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    // Verhaal uit dezelfde bestemming -> zichtbaar.
    Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Een verhaal uit deze reis',
    ]);

    // Tip uit dezelfde bestemming -> niet in de strook.
    $tip = Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Een reistip die hier niet hoort',
    ]);
    $tip->categories()->attach($tips);

    // Verhaal uit een andere bestemming -> niet in de strook.
    $other = Destination::factory()->create(['slug' => 'schotland', 'name' => 'Schotland']);
    $otherLocation = Location::factory()->for($other)->create(['slug' => 'edinburgh', 'name' => 'Edinburgh']);
    Post::factory()->published()->create([
        'destination_id' => $other->id,
        'location_id' => $otherLocation->id,
        'title' => 'Een verhaal uit een andere reis',
    ]);

    get('/reisroutes/italie-roadtrip')
        ->assertOk()
        ->assertSee('Verhalen van deze reis')
        ->assertSee('Een verhaal uit deze reis')
        ->assertDontSee('Een reistip die hier niet hoort')
        ->assertDontSee('Een verhaal uit een andere reis');
});
