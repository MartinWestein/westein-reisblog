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
