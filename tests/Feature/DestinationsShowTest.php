<?php

use App\Models\Destination;
use App\Models\Location;

use function Pest\Laravel\get;

it('renders the destination detail page for guests', function () {
    Destination::factory()->create([
        'name' => 'Testland',
        'slug' => 'testland',
        'description' => 'Een uitgebreide beschrijving over deze bestemming vol details.',
    ]);

    get('/bestemmingen/testland')
        ->assertOk()
        ->assertSee('Testland')
        ->assertSee('Een uitgebreide beschrijving over deze bestemming vol details.');
});

it('returns 404 for an unknown destination slug', function () {
    get('/bestemmingen/onbekende-slug')->assertNotFound();
});

it('shows the section label and h1 heading for the destination', function () {
    Destination::factory()->create([
        'name' => 'Portugal',
        'slug' => 'portugal',
    ]);

    get('/bestemmingen/portugal')
        ->assertOk()
        ->assertSee('Bestemming')
        ->assertSee('Portugal');
});

it('renders all locations belonging to the destination in id order', function () {
    $destination = Destination::factory()->create([
        'name' => 'Spanje',
        'slug' => 'spanje',
    ]);

    Location::factory()->for($destination)->create(['name' => 'Barcelona']);
    Location::factory()->for($destination)->create(['name' => 'Madrid']);
    Location::factory()->for($destination)->create(['name' => 'Sevilla']);

    get('/bestemmingen/spanje')
        ->assertOk()
        ->assertSeeInOrder(['Barcelona', 'Madrid', 'Sevilla']);
});

it('does not leak locations from other destinations', function () {
    $target = Destination::factory()->create(['slug' => 'target', 'name' => 'Target']);
    $other = Destination::factory()->create(['slug' => 'other', 'name' => 'Other']);

    Location::factory()->for($target)->create(['name' => 'PlekVanTarget']);
    Location::factory()->for($other)->create(['name' => 'PlekVanOther']);

    get('/bestemmingen/target')
        ->assertOk()
        ->assertSee('PlekVanTarget')
        ->assertDontSee('PlekVanOther');
});

it('renders the back CTA linking to the destinations index', function () {
    Destination::factory()->create(['slug' => 'ergens', 'name' => 'Ergens']);

    get('/bestemmingen/ergens')
        ->assertOk()
        ->assertSee('Alle bestemmingen bekijken')
        ->assertSee(route('destinations.index'), false);
});

it('shows the locations section label and heading when destination has locations', function () {
    $destination = Destination::factory()->create(['slug' => 'ietsanders', 'name' => 'IetsAnders']);
    Location::factory()->for($destination)->create();

    get('/bestemmingen/ietsanders')
        ->assertOk()
        ->assertSee('Onderweg')
        ->assertSee('Plekken die we bezochten');
});

it('renders a breadcrumb at the top with the destinations index link', function () {
    Destination::factory()->create(['slug' => 'kruimeltest', 'name' => 'Kruimeltest']);

    $response = get('/bestemmingen/kruimeltest')->assertOk();

    $response->assertSee('aria-label="Kruimelspoor"', false);
    $response->assertSee(route('destinations.index'), false);
    $response->assertSee('Kruimeltest');
});
