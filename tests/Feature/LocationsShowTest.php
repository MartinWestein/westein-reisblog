<?php

use App\Models\Destination;
use App\Models\Location;

use function Pest\Laravel\get;

it('renders the location detail page for guests', function () {
    $destination = Destination::factory()->create([
        'name' => 'Testland',
        'slug' => 'testland',
    ]);
    Location::factory()->for($destination)->create([
        'name' => 'Teststad',
        'slug' => 'teststad',
        'description' => 'Een beschrijving van deze prachtige plek vol details.',
    ]);

    get('/bestemmingen/testland/teststad')
        ->assertOk()
        ->assertSee('Teststad')
        ->assertSee('Een beschrijving van deze prachtige plek vol details.');
});

it('returns 404 for an unknown location slug', function () {
    Destination::factory()->create(['slug' => 'bekend', 'name' => 'Bekend']);

    get('/bestemmingen/bekend/onbekende-plek')->assertNotFound();
});

it('returns 404 when the location does not belong to the parent destination', function () {
    Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $slovenia = Destination::factory()->create(['slug' => 'slovenie', 'name' => 'Slovenie']);

    Location::factory()->for($slovenia)->create(['slug' => 'ljubljana', 'name' => 'Ljubljana']);

    get('/bestemmingen/italie/ljubljana')->assertNotFound();
});

it('shows the section label and h1 heading for the location', function () {
    $destination = Destination::factory()->create(['name' => 'Portugal', 'slug' => 'portugal']);
    Location::factory()->for($destination)->create([
        'name' => 'Lissabon',
        'slug' => 'lissabon',
    ]);

    get('/bestemmingen/portugal/lissabon')
        ->assertOk()
        ->assertSee('Plek')
        ->assertSee('Lissabon');
});

it('renders the back CTA linking to the parent destination', function () {
    $destination = Destination::factory()->create(['slug' => 'ergens', 'name' => 'Ergens']);
    Location::factory()->for($destination)->create(['slug' => 'eenplek', 'name' => 'Eenplek']);

    get('/bestemmingen/ergens/eenplek')
        ->assertOk()
        ->assertSee('Terug naar Ergens')
        ->assertSee(route('destinations.show', $destination), false);
});

it('renders a breadcrumb at the top with links to the index and parent destination', function () {
    $destination = Destination::factory()->create(['slug' => 'kruimel', 'name' => 'Kruimel']);
    Location::factory()->for($destination)->create(['slug' => 'kruimelplek', 'name' => 'Kruimelplek']);

    $response = get('/bestemmingen/kruimel/kruimelplek')->assertOk();

    $response->assertSee('aria-label="Kruimelspoor"', false);
    $response->assertSee(route('destinations.index'), false);
    $response->assertSee(route('destinations.show', $destination), false);
    $response->assertSee('Kruimelplek');
});

it('shows the location name and parent destination in the page title', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);

    get('/bestemmingen/italie/rome')
        ->assertOk()
        ->assertSee('<title>Rome — Italie — '.config('app.name').'</title>', false);
});

it('does not render the gallery section when the location has no media', function () {
    $destination = Destination::factory()->create(['slug' => 'zonderfoto', 'name' => 'Zonderfoto']);
    Location::factory()->for($destination)->create(['slug' => 'leegplek', 'name' => 'Leegplek']);

    get('/bestemmingen/zonderfoto/leegplek')
        ->assertOk()
        ->assertDontSee('Impressies');
});

it('toont de kaart-container met coördinaten', function () {
    $destination = Destination::factory()->create(['name' => 'Italië', 'slug' => 'italie']);
    Location::factory()->for($destination)->create([
        'name' => 'Rome',
        'slug' => 'rome',
        'latitude' => 41.9028,
        'longitude' => 12.4964,
    ]);

    get('/bestemmingen/italie/rome')
        ->assertOk()
        ->assertSee('data-location-map', false)
        ->assertSee('41.9028000')
        ->assertSee('12.4964000');
});

it('verbergt de kaart zonder coördinaten', function () {
    $destination = Destination::factory()->create(['name' => 'Italië', 'slug' => 'italie']);
    Location::factory()->for($destination)->create([
        'name' => 'Rome',
        'slug' => 'rome',
        'latitude' => null,
        'longitude' => null,
    ]);

    get('/bestemmingen/italie/rome')
        ->assertOk()
        ->assertDontSee('data-location-map', false);
});
