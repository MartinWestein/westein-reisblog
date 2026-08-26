<?php

use App\Models\Destination;
use App\Models\FamilyMember;
use App\Models\Location;
use App\Models\Post;

use function Pest\Laravel\get;

it('rendert WebSite + Organization JSON-LD site-breed', function () {
    get('/')
        ->assertOk()
        ->assertSee('application/ld+json', false)
        ->assertSee('"@type":"WebSite"', false)
        ->assertSee('"@type":"Organization"', false)
        ->assertSee('images/logo.png', false);
});

it('rendert een BreadcrumbList op een detail-pagina met kruimelpad', function () {
    Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);

    get('/bestemmingen/italie')
        ->assertOk()
        ->assertSee('"@type":"BreadcrumbList"', false)
        ->assertSee('"@type":"ListItem"', false);
});

it('rendert Article (BlogPosting) JSON-LD op een post-detail', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $post = Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Onze eerste dag in Rome',
    ]);

    get('/bestemmingen/italie/rome/'.$post->slug)
        ->assertOk()
        ->assertSee('"@type":"BlogPosting"', false)
        ->assertSee('"@type":"Person"', false);
});

it('rendert Place JSON-LD met GeoCoordinates op een location-detail', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create([
        'slug' => 'rome', 'name' => 'Rome',
        'latitude' => 41.9028, 'longitude' => 12.4964,
    ]);

    get('/bestemmingen/italie/rome')
        ->assertOk()
        ->assertSee('"@type":"TouristAttraction"', false)
        ->assertSee('"@type":"GeoCoordinates"', false)
        ->assertSee('41.9028', false);
});

it('rendert TouristDestination JSON-LD op een destination-detail', function () {
    Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);

    get('/bestemmingen/italie')
        ->assertOk()
        ->assertSee('"@type":"TouristDestination"', false);
});

it('rendert Person JSON-LD op een auteur-pagina', function () {
    FamilyMember::factory()->create([
        'slug' => 'jan-westein', 'name' => 'Jan Westein',
        'bio' => 'Een korte biografie over Jan.',
    ]);

    get('/auteurs/jan-westein')
        ->assertOk()
        ->assertSee('"@type":"Person"', false)
        ->assertSee('Jan Westein', false);
});
