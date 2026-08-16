<?php

use App\Models\Category;
use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;

use function Pest\Laravel\get;

it('toont de detail-pagina van een gepubliceerde location-post', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $post = Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Onze eerste dag in Rome',
        'excerpt' => 'Een korte intro over onze aankomst in de eeuwige stad.',
        'body' => '<p>Dit is de volledige inhoud van het verhaal over Rome.</p>',
    ]);

    get('/bestemmingen/italie/rome/'.$post->slug)
        ->assertOk()
        ->assertSee('Onze eerste dag in Rome')
        ->assertSee('Dit is de volledige inhoud van het verhaal over Rome.', false);
});

it('toont de detail-pagina van een gepubliceerde reistip', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);
    $post = Post::factory()->tipsGeneral()->published()->create([
        'title' => 'Reizen met kleine kinderen',
        'body' => '<p>Praktische tips voor onderweg met kinderen.</p>',
    ]);
    $post->categories()->attach($tips);

    get('/reistips/'.$post->slug)
        ->assertOk()
        ->assertSee('Reizen met kleine kinderen')
        ->assertSee('Praktische tips voor onderweg met kinderen.', false);
});

it('geeft 404 voor een draft location-post', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $post = Post::factory()->draft()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
    ]);

    get('/bestemmingen/italie/rome/'.$post->slug)->assertNotFound();
});

it('geeft 404 voor een geplande (toekomstige) location-post', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $post = Post::factory()->scheduled()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
    ]);

    get('/bestemmingen/italie/rome/'.$post->slug)->assertNotFound();
});

it('geeft 404 voor een niet-gepubliceerde reistip', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);
    $post = Post::factory()->tipsGeneral()->draft()->create();
    $post->categories()->attach($tips);

    get('/reistips/'.$post->slug)->assertNotFound();
});

it('weigert een tip via de bestemmingen-boom-URL (canonieke handhaving)', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);
    $destination = Destination::factory()->create(['slug' => 'slovenie', 'name' => 'Slovenie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'bled', 'name' => 'Bled']);
    $post = Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Beste fotospots in Bled',
    ]);
    $post->categories()->attach($tips);

    // Canoniek is /reistips/beste-fotospots-in-bled; de boom-URL moet 404 geven.
    get('/bestemmingen/slovenie/bled/'.$post->slug)->assertNotFound();
});

it('weigert een niet-tip via de /reistips-URL (canonieke handhaving)', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $post = Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Onze eerste dag in Rome',
    ]);

    get('/reistips/'.$post->slug)->assertNotFound();
});

it('geeft 404 wanneer de post niet bij de opgegeven location hoort (scopeBindings)', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $rome = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $florence = Location::factory()->for($destination)->create(['slug' => 'florence', 'name' => 'Florence']);
    $post = Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $rome->id,
        'title' => 'Post over Rome',
    ]);

    // Post hoort bij Rome; opvragen via Florence moet 404 geven.
    get('/bestemmingen/italie/florence/'.$post->slug)->assertNotFound();
});
