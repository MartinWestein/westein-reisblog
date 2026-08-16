<?php

use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;

use function Pest\Laravel\get;

it('toont de verhalen-index met gepubliceerde posts', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Een gepubliceerd verhaal',
    ]);

    get('/verhalen')
        ->assertOk()
        ->assertSee('Verhalen')
        ->assertSee('Een gepubliceerd verhaal');
});

it('toont geen draft-posts op de index', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    Post::factory()->draft()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Een verborgen concept',
    ]);

    get('/verhalen')
        ->assertOk()
        ->assertDontSee('Een verborgen concept');
});

it('pagineert de index bij meer dan 12 posts', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);

    // 15 gepubliceerde posts -> 2 pagina's (12 + 3).
    Post::factory()->count(15)->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
    ]);

    // Pagina 1 toont de paginering-link naar pagina 2.
    get('/verhalen')
        ->assertOk()
        ->assertSee('page=2', false);

    // Pagina 2 is bereikbaar.
    get('/verhalen?page=2')->assertOk();
});
