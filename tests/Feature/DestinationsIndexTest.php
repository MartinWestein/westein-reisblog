<?php

use App\Models\Destination;
use App\Models\Location;

use function Pest\Laravel\get;

it('renders the destinations index for guests', function () {
    get('/bestemmingen')
        ->assertOk()
        ->assertSee('Ontdek alle bestemmingen van familie Westein', false)
        ->assertSee('Elke bestemming die je hier vindt hebben we bezocht met de familie.');
});

it('shows a destination with name, description snippet and location count', function () {
    $destination = Destination::factory()->create([
        'name' => 'Testland',
        'description' => 'Een korte beschrijving over deze bestemming.',
        'is_featured' => false,
    ]);

    Location::factory()->for($destination)->count(3)->create();

    get('/bestemmingen')
        ->assertOk()
        ->assertSee('Testland')
        ->assertSee('Een korte beschrijving over deze bestemming.')
        ->assertSee('3 plekken');
});

it('uses singular label when a destination has exactly one location', function () {
    $destination = Destination::factory()->create([
        'name' => 'Enkeltje',
        'is_featured' => false,
    ]);

    Location::factory()->for($destination)->create();

    get('/bestemmingen')
        ->assertOk()
        ->assertSee('Enkeltje')
        ->assertSee('1 plek')
        ->assertDontSee('1 plekken');
});

it('shows a featured badge on is_featured destinations', function () {
    Destination::factory()->create([
        'name' => 'Uitgekozenland',
        'is_featured' => true,
    ]);

    Destination::factory()->create([
        'name' => 'Gewoonland',
        'is_featured' => false,
    ]);

    get('/bestemmingen')
        ->assertOk()
        ->assertSee('Uitgelicht');
});

it('sorts featured destinations first, then non-featured by newest created', function () {
    Destination::factory()->create([
        'name' => 'AlfaOud',
        'is_featured' => false,
        'created_at' => now()->subDays(2),
    ]);
    Destination::factory()->create([
        'name' => 'BetaFeatured',
        'is_featured' => true,
        'created_at' => now()->subDays(1),
    ]);
    Destination::factory()->create([
        'name' => 'GammaNieuw',
        'is_featured' => false,
        'created_at' => now(),
    ]);

    get('/bestemmingen')
        ->assertOk()
        ->assertSeeInOrder(['BetaFeatured', 'GammaNieuw', 'AlfaOud']);
});

it('shows an empty state when there are no destinations', function () {
    get('/bestemmingen')
        ->assertOk()
        ->assertSee('Er zijn nog geen bestemmingen gepubliceerd.');
});
