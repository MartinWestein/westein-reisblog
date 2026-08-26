<?php

use App\Models\Category;
use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

it('shows destination-bound tips in the tips strook linking to reistips', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);

    $tip = Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Fotospots in Rome',
    ]);
    $tip->categories()->attach($tips);

    get('/bestemmingen/italie')
        ->assertOk()
        ->assertSee('Reistips voor deze reis')
        ->assertSee('Fotospots in Rome')
        ->assertSee(route('reistips.show', $tip), false);
});

it('does not show tips from other destinations in the tips strook', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);
    Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $slovenie = Destination::factory()->create(['slug' => 'slovenie', 'name' => 'Slovenie']);
    $bled = Location::factory()->for($slovenie)->create(['slug' => 'bled', 'name' => 'Bled']);

    $otherTip = Post::factory()->published()->create([
        'destination_id' => $slovenie->id,
        'location_id' => $bled->id,
        'title' => 'Fotospots in Bled',
    ]);
    $otherTip->categories()->attach($tips);

    get('/bestemmingen/italie')
        ->assertOk()
        ->assertDontSee('Fotospots in Bled');
});

it('does not show unpublished tips in the tips strook', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);

    $draftTip = Post::factory()->draft()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Nog niet af tip',
    ]);
    $draftTip->categories()->attach($tips);

    get('/bestemmingen/italie')
        ->assertOk()
        ->assertDontSee('Nog niet af tip');
});

it('does not show regular non-tip posts in the tips strook', function () {
    Category::factory()->create(['name' => 'Tips']);
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);

    Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Een gewoon reisverhaal',
    ]);

    get('/bestemmingen/italie')
        ->assertOk()
        ->assertDontSee('Reistips voor deze reis');
});

it('hides the tips section when the destination has no tips', function () {
    Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);

    get('/bestemmingen/italie')
        ->assertOk()
        ->assertDontSee('Reistips voor deze reis')
        ->assertDontSee('destination-detail__tips', false);
});

it('gebruikt de hero als og:image en blijft og:type=website', function () {
    Storage::fake('public');
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $destination->addMedia(UploadedFile::fake()->image('hero.jpg', 2000, 1000))->toMediaCollection('hero');

    get('/bestemmingen/italie')
        ->assertOk()
        ->assertSee('property="og:type" content="website"', false)
        ->assertDontSee('images/og-default.jpg', false);
});
