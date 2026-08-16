<?php

use App\Models\Category;
use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Carbon;

test('post genereert automatisch een slug uit title', function () {
    $post = Post::factory()->create(['title' => 'Onze eerste dag in Rome']);

    expect($post->slug)->toBe('onze-eerste-dag-in-rome');
});

test('post-slug blijft stabiel na hernoemen', function () {
    $post = Post::factory()->create(['title' => 'Roadtrip door Toscane']);
    $originalSlug = $post->slug;

    $post->update(['title' => 'Roadtrip door heel Toscane']);

    expect($post->fresh()->slug)->toBe($originalSlug);
});

test('post behoort tot een author', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create();

    expect($post->author)->toBeInstanceOf(User::class)
        ->and($post->author->id)->toBe($user->id);
});

test('post kan behoren tot een destination', function () {
    $destination = Destination::factory()->create();
    $post = Post::factory()->create([
        'destination_id' => $destination->id,
        'location_id' => null,
    ]);

    expect($post->destination)->toBeInstanceOf(Destination::class)
        ->and($post->location)->toBeNull();
});

test('post kan behoren tot een location', function () {
    $location = Location::factory()->create();
    $post = Post::factory()->create([
        'location_id' => $location->id,
        'destination_id' => $location->destination_id,
    ]);

    expect($post->location)->toBeInstanceOf(Location::class)
        ->and($post->location->id)->toBe($location->id);
});

test('post kan beide destination_id en location_id leeg hebben (tip-scenario)', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user, 'author')->create([
        'destination_id' => null,
        'location_id' => null,
    ]);

    expect($post->destination)->toBeNull()
        ->and($post->location)->toBeNull();
});

test('post kan meerdere categorieën hebben', function () {
    $post = Post::factory()->create();
    $verslag = Category::create(['name' => 'Verslag', 'order' => 1]);
    $eten = Category::create(['name' => 'Eten', 'order' => 2]);

    $post->categories()->attach([$verslag->id, $eten->id]);

    expect($post->categories)->toHaveCount(2)
        ->and($post->categories->pluck('name')->all())->toContain('Verslag', 'Eten');
});

test('post kan tags hebben via HasTags-trait', function () {
    $post = Post::factory()->create();
    $camper = Tag::create(['name' => 'camper']);
    $italie = Tag::create(['name' => 'italie']);

    $post->tags()->attach([$camper->id, $italie->id]);

    expect($post->tags)->toHaveCount(2);
});

test('post-status is standaard draft', function () {
    $post = Post::factory()->create();

    expect($post->status)->toBe('draft')
        ->and($post->published_at)->toBeNull();
});

test('post published state geeft gepubliceerde post', function () {
    $post = Post::factory()->published()->create();

    expect($post->status)->toBe('published')
        ->and($post->published_at)->not->toBeNull();
});

test('post wordt verwijderd als de categorie-koppeling wordt verbroken', function () {
    $post = Post::factory()->create();
    $category = Category::create(['name' => 'Verslag', 'order' => 1]);

    $post->categories()->attach($category);
    expect($post->categories)->toHaveCount(1);

    $category->delete();

    // Pivot-rij weg (cascade), maar post blijft bestaan
    expect($post->fresh()->categories)->toHaveCount(0)
        ->and(Post::find($post->id))->not->toBeNull();
});

test('post published_at wordt naar Carbon gecast', function () {
    $post = Post::factory()->published()->create();

    expect($post->published_at)->toBeInstanceOf(Carbon::class);
});

// ==== TOEVOEGEN AAN tests/Feature/Models/PostTest.php ====
// Plak deze test-blokken onderaan het bestand (vóór de laatste regel als die leeg is).
// Vereist bovenaan de use-imports: Category, Destination, Location, Post — die staan er al.

test('url() geeft de bestemmingen-boom-URL voor een location-post', function () {
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);
    $post = Post::factory()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Onze eerste dag in Rome',
    ]);

    expect($post->url())->toBe(url('/bestemmingen/italie/rome/onze-eerste-dag-in-rome'));
});

test('url() geeft de reistips-URL voor een tip met destination (categorie leidend)', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);
    $destination = Destination::factory()->create(['slug' => 'schotland', 'name' => 'Schotland']);
    $location = Location::factory()->for($destination)->create(['slug' => 'glencoe', 'name' => 'Glencoe']);
    $post = Post::factory()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Camperen in Schotland met kinderen',
    ]);
    $post->categories()->attach($tips);

    expect($post->url())->toBe(url('/reistips/camperen-in-schotland-met-kinderen'));
});

test('url() geeft de reistips-URL voor een algemene tip zonder destination', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);
    $post = Post::factory()->tipsGeneral()->create([
        'title' => 'Reizen met kleine kinderen',
    ]);
    $post->categories()->attach($tips);

    expect($post->url())->toBe(url('/reistips/reizen-met-kleine-kinderen'));
});

test('url() gooit een exception voor een location-loze niet-tip-post', function () {
    $post = Post::factory()->forDestinationOnly()->create([
        'title' => 'Verweesde post',
    ]);

    expect(fn () => $post->url())->toThrow(LogicException::class);
});

test('isPublished() is waar voor een gepubliceerde post in het verleden', function () {
    $post = Post::factory()->published()->create();

    expect($post->isPublished())->toBeTrue();
});

test('isPublished() is onwaar voor een draft', function () {
    $post = Post::factory()->draft()->create();

    expect($post->isPublished())->toBeFalse();
});

test('isPublished() is onwaar voor een geplande post in de toekomst', function () {
    $post = Post::factory()->scheduled()->create();

    expect($post->isPublished())->toBeFalse();
});

test('scopePublished levert alleen gepubliceerde posts in het verleden', function () {
    Post::factory()->published()->create();
    Post::factory()->draft()->create();
    Post::factory()->scheduled()->create();

    expect(Post::published()->count())->toBe(1);
});
