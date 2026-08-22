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

it('toont de hero-placeholder wanneer een post geen featured image heeft', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $post = Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
    ]);

    get('/bestemmingen/italie/rome/'.$post->slug)
        ->assertOk()
        ->assertSee('post-detail__hero-placeholder', false);
});

it('toont een 4-niveau breadcrumb op een location-post', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $post = Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Onze eerste dag in Rome',
    ]);

    // De location-crumb-URL komt alleen in de breadcrumb voor (nav/kaarten linken er niet naar).
    get('/bestemmingen/italie/rome/'.$post->slug)
        ->assertOk()
        ->assertSee(route('locations.show', [$destination, $location]), false)
        ->assertSee('Rome')
        ->assertSee('aria-current="page"', false);
});

it('toont een reistip-breadcrumb met een Reistips-kruimel', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);
    $post = Post::factory()->tipsGeneral()->published()->create([
        'title' => 'Reizen met kleine kinderen',
    ]);
    $post->categories()->attach($tips);

    get('/reistips/'.$post->slug)
        ->assertOk()
        ->assertSee('Kruimelspoor', false)
        ->assertSee('Reistips')
        ->assertSee('aria-current="page"', false);
});

it('gebruikt meta_title en meta_description als override in de head', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $post = Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Onze eerste dag in Rome',
        'excerpt' => 'Zichtbare intro-tekst op de pagina.',
        'meta_title' => 'Aangepaste SEO-titel voor Rome',
        'meta_description' => 'Een op maat gemaakte metabeschrijving voor zoekmachines.',
    ]);

    get('/bestemmingen/italie/rome/'.$post->slug)
        ->assertOk()
        ->assertSee('<title>Aangepaste SEO-titel voor Rome', false)
        ->assertSee('Een op maat gemaakte metabeschrijving voor zoekmachines.', false);
});

it('toont gerelateerde posts uit dezelfde reis en sluit tips en andere reizen uit', function () {
    $italie = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $rome = Location::factory()->for($italie)->create(['slug' => 'rome', 'name' => 'Rome']);
    $florence = Location::factory()->for($italie)->create(['slug' => 'florence', 'name' => 'Florence']);

    $current = Post::factory()->published()->create([
        'destination_id' => $italie->id, 'location_id' => $rome->id,
        'title' => 'Onze eerste dag in Rome',
    ]);
    Post::factory()->published()->create([
        'destination_id' => $italie->id, 'location_id' => $florence->id,
        'title' => 'Een dag in Florence',
    ]);

    // Tip in dezelfde destination -> uitgesloten.
    $tips = Category::factory()->create(['name' => 'Tips']);
    $tipSameDest = Post::factory()->published()->create([
        'destination_id' => $italie->id, 'location_id' => $rome->id,
        'title' => 'Fotospots in Italie',
    ]);
    $tipSameDest->categories()->attach($tips);

    // Post in een andere reis -> uitgesloten.
    $slovenie = Destination::factory()->create(['slug' => 'slovenie', 'name' => 'Slovenie']);
    $bled = Location::factory()->for($slovenie)->create(['slug' => 'bled', 'name' => 'Bled']);
    Post::factory()->published()->create([
        'destination_id' => $slovenie->id, 'location_id' => $bled->id,
        'title' => 'Het meer van Bled',
    ]);

    get('/bestemmingen/italie/rome/'.$current->slug)
        ->assertOk()
        ->assertSee('Meer uit deze reis')
        ->assertSee('Een dag in Florence')
        ->assertDontSee('Fotospots in Italie')
        ->assertDontSee('Het meer van Bled');
});

it('toont andere reistips als gerelateerd op een reistip', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);

    $current = Post::factory()->tipsGeneral()->published()->create([
        'title' => 'Reizen met kleine kinderen',
    ]);
    $current->categories()->attach($tips);

    $otherTip = Post::factory()->tipsGeneral()->published()->create([
        'title' => 'Handbagage slim inpakken',
    ]);
    $otherTip->categories()->attach($tips);

    get('/reistips/'.$current->slug)
        ->assertOk()
        ->assertSee('Andere reistips')
        ->assertSee('Handbagage slim inpakken');
});

it('verbergt de gerelateerde-sectie als er geen gerelateerde posts zijn', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $post = Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Onze enige post',
    ]);

    get('/bestemmingen/italie/rome/'.$post->slug)
        ->assertOk()
        ->assertDontSee('Meer uit deze reis')
        ->assertDontSee('post-detail__related', false);
});

it('linkt de Reistips-breadcrumb-kruimel naar de reistips-index', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);
    $post = Post::factory()->tipsGeneral()->published()->create([
        'title' => 'Reizen met kleine kinderen',
    ]);
    $post->categories()->attach($tips);

    get('/reistips/'.$post->slug)
        ->assertOk()
        ->assertSee('href="'.route('reistips.index').'"', false);
});
