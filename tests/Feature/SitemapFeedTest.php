<?php

use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;

it('genereert een sitemap.xml met publieke URLs', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
    ]);

    $this->artisan('sitemap:generate')->assertSuccessful();

    $path = public_path('sitemap.xml');
    expect(file_exists($path))->toBeTrue();

    $xml = file_get_contents($path);
    expect($xml)->toContain('/bestemmingen/italie');
    expect($xml)->toContain('/bestemmingen/italie/rome');

    @unlink($path);
});

it('rendert de RSS-feed met gepubliceerde verhalen', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Onze eerste dag in Rome',
    ]);

    $this->get('/feed')
        ->assertOk()
        ->assertSee('<rss', false)
        ->assertSee('Onze eerste dag in Rome', false);
});
