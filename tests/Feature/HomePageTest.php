<?php

use App\Models\Destination;
use App\Models\Post;
use App\Models\Route;
use App\Models\User;

use function Pest\Laravel\get;

it('renders the homepage for guests', function () {
    get('/')
        ->assertOk()
        ->assertSee('Onze Reisverhalen');
});

it('renders the homepage with brand and site-nav for guests', function () {
    get('/')
        ->assertOk()
        ->assertSee('Westein Reisblog')     // blog-nav tekst-brand
        ->assertSee('Bestemmingen')          // blog-nav menu-item
        ->assertSee('Reistips')
        ->assertSee('Inloggen');             // uitgelogd
});

it('renders the homepage with footer', function () {
    get('/')
        ->assertOk()
        ->assertSee('Onze reizen, verhalen en foto\'s', false)  // footer tagline
        ->assertSee('Ontdek')                                    // footer kolom
        ->assertSee('Info')                                      // footer kolom
        ->assertSee('Familie Westein');                          // copyright
});

it('has the correct route name', function () {
    expect(route('home'))->toBe(url('/'));
});

it('renders the hero block with title, tagline and image', function () {
    get('/')
        ->assertOk()
        ->assertSee('Onze Reisverhalen')
        ->assertSee('Onze reizen, verhalen en foto\'s', false)
        ->assertSee('familie Westein onderweg')       // alt-tekst
        ->assertSee('images/hero-home.jpg');       // image src
});

it('shows the featured destination when one exists', function () {
    $destination = Destination::factory()->create(['name' => 'Toscane']);

    get('/')
        ->assertOk()
        ->assertSee('Uitgelichte bestemming')
        ->assertSee('Toscane')
        ->assertSee('Meer over Toscane');
});

it('omits the featured destination block when no destinations exist', function () {
    Destination::query()->forceDelete();

    get('/')
        ->assertOk()
        ->assertDontSee('Uitgelichte bestemming');
});

it('shows latest posts when published posts exist', function () {
    $author = User::factory()->create(['name' => 'Anne Reiziger']);
    $destination = Destination::factory()->create(['name' => 'Provence']);

    Post::factory()->create([
        'title' => 'Zomer in de lavendel',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'user_id' => $author->id,
        'destination_id' => $destination->id,
    ]);

    get('/')
        ->assertOk()
        ->assertSee('Laatste verhalen')
        ->assertSee('Zomer in de lavendel')
        ->assertSee('Anne Reiziger')
        ->assertSee('Provence');
});

it('excludes non-published posts from the homepage', function () {
    Post::factory()->create([
        'title' => 'Draft-verhaal',
        'status' => 'draft',
        'published_at' => null,
    ]);

    Post::factory()->create([
        'title' => 'Toekomstig verhaal',
        'status' => 'published',
        'published_at' => now()->addWeek(),
    ]);

    get('/')
        ->assertOk()
        ->assertDontSee('Draft-verhaal')
        ->assertDontSee('Toekomstig verhaal');
});

it('shows featured routes when published routes exist', function () {
    $destination = Destination::factory()->create(['name' => 'Toscane']);

    Route::factory()->create([
        'name' => 'Toscaanse Heuvels Roadtrip',
        'destination_id' => $destination->id,
        'is_published' => true,
        'published_at' => now()->subMonth(),
        'travel_date' => now()->subMonths(3),
    ]);

    get('/')
        ->assertOk()
        ->assertSee('Uitgelichte reisroutes')
        ->assertSee('Toscaanse Heuvels Roadtrip');
});

it('excludes unpublished routes from the homepage', function () {
    Route::factory()->create([
        'name' => 'Onbekende Route',
        'is_published' => false,
        'published_at' => null,
    ]);

    get('/')
        ->assertOk()
        ->assertDontSee('Onbekende Route');
});

it('shows the CTA strip', function () {
    get('/')
        ->assertOk()
        ->assertSee('Ontdek al onze bestemmingen')
        ->assertSee('Bekijk alle bestemmingen');
});
