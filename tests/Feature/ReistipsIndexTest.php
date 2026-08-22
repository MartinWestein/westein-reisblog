<?php

use App\Models\Category;
use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;

use function Pest\Laravel\get;

it('toont de reistips-index met gepubliceerde tips', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);

    // Bestemming-gebonden tip (F5-69).
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $boundTip = Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Fotospots in Rome',
    ]);
    $boundTip->categories()->attach($tips);

    // Algemene tip (geen destination/location) (F5-69).
    $generalTip = Post::factory()->tipsGeneral()->published()->create([
        'title' => 'Reizen met kleine kinderen',
    ]);
    $generalTip->categories()->attach($tips);

    get('/reistips')
        ->assertOk()
        ->assertSee('Reistips')
        ->assertSee('Fotospots in Rome')
        ->assertSee('Reizen met kleine kinderen');
});

it('toont geen niet-gepubliceerde tips op de reistips-index', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);
    $tip = Post::factory()->tipsGeneral()->draft()->create([
        'title' => 'Een verborgen concept-tip',
    ]);
    $tip->categories()->attach($tips);

    get('/reistips')
        ->assertOk()
        ->assertDontSee('Een verborgen concept-tip');
});

it('toont geen gewone verhalen op de reistips-index', function () {
    Category::factory()->create(['name' => 'Tips']);
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Een gewoon reisverhaal',
    ]);

    get('/reistips')
        ->assertOk()
        ->assertDontSee('Een gewoon reisverhaal');
});

it('pagineert de reistips-index bij meer dan 12 tips', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);
    // 15 gepubliceerde tips -> 2 pagina's (12 + 3).
    Post::factory()->count(15)->tipsGeneral()->published()->create()
        ->each(fn ($tip) => $tip->categories()->attach($tips));

    get('/reistips')
        ->assertOk()
        ->assertSee('page=2', false);

    get('/reistips?page=2')->assertOk();
});
