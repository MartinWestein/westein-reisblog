<?php

use App\Models\Comment;
use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    foreach (['admin', 'editor', 'auteur', 'lid'] as $roleName) {
        Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    }
    // Honeypot uit in tests: we toetsen onze eigen logica, niet Spatie's middleware.
    config(['honeypot.enabled' => false]);
});

function publishedLocationPost(): Post
{
    $destination = Destination::factory()->create();
    $location = Location::factory()->for($destination)->create();

    return Post::factory()->published()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
    ]);
}

/*
|--------------------------------------------------------------------------
| Write-path
|--------------------------------------------------------------------------
*/

it('een ingelogd lid plaatst een top-level reactie die op moderatie wacht', function () {
    $post = publishedLocationPost();
    $lid = User::factory()->create();
    $lid->assignRole('lid');

    actingAs($lid)
        ->post(route('comments.store', $post), ['body' => 'Wat een mooi verhaal!'])
        ->assertRedirect()
        ->assertSessionHas('comment_success');

    $comment = Comment::first();
    expect($comment)->not->toBeNull()
        ->and($comment->status)->toBe('pending')
        ->and($comment->user_id)->toBe($lid->id)
        ->and($comment->post_id)->toBe($post->id)
        ->and($comment->parent_id)->toBeNull();
});

it('een admin plaatst een reactie die direct is goedgekeurd', function () {
    $post = publishedLocationPost();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    actingAs($admin)->post(route('comments.store', $post), ['body' => 'Mooi verhaal!']);

    $comment = Comment::first();
    expect($comment->status)->toBe('approved')
        ->and($comment->approved_at)->not->toBeNull();
});

it('een uitgelogde bezoeker kan geen reactie plaatsen', function () {
    $post = publishedLocationPost();

    post(route('comments.store', $post), ['body' => 'Hoi'])
        ->assertRedirect(route('login'));

    expect(Comment::count())->toBe(0);
});

it('een lid plaatst een reply op een bestaande hoofdreactie', function () {
    $post = publishedLocationPost();
    $lid = User::factory()->create();
    $lid->assignRole('lid');
    $parent = Comment::factory()->approved()->create(['post_id' => $post->id]);

    actingAs($lid)->post(route('comments.store', $post), [
        'body' => 'Helemaal mee eens!',
        'parent_id' => $parent->id,
    ])->assertRedirect();

    $reply = Comment::where('parent_id', $parent->id)->first();
    expect($reply)->not->toBeNull()
        ->and($reply->parent_id)->toBe($parent->id);
});

it('weigert een reactie zonder body', function () {
    $post = publishedLocationPost();
    $lid = User::factory()->create();
    $lid->assignRole('lid');

    actingAs($lid)->post(route('comments.store', $post), ['body' => ''])
        ->assertSessionHasErrorsIn('comment', ['body']);

    expect(Comment::count())->toBe(0);
});

it('weigert een reply waarvan de parent bij een andere post hoort', function () {
    $post = publishedLocationPost();
    $otherPost = publishedLocationPost();
    $lid = User::factory()->create();
    $lid->assignRole('lid');
    $foreignParent = Comment::factory()->approved()->create(['post_id' => $otherPost->id]);

    actingAs($lid)->post(route('comments.store', $post), [
        'body' => 'Test',
        'parent_id' => $foreignParent->id,
    ])->assertSessionHasErrorsIn('comment', ['parent_id']);

    expect(Comment::where('body', 'Test')->exists())->toBeFalse();
});

it('weigert een reply op een reply (max 1 niveau)', function () {
    $post = publishedLocationPost();
    $lid = User::factory()->create();
    $lid->assignRole('lid');
    $top = Comment::factory()->approved()->create(['post_id' => $post->id]);
    $reply = Comment::factory()->approved()->create([
        'post_id' => $post->id,
        'parent_id' => $top->id,
    ]);

    actingAs($lid)->post(route('comments.store', $post), [
        'body' => 'Te diep',
        'parent_id' => $reply->id,
    ])->assertSessionHasErrorsIn('comment', ['parent_id']);
});

it('geeft 404 bij een reactie op een niet-gepubliceerde post', function () {
    $destination = Destination::factory()->create();
    $location = Location::factory()->for($destination)->create();
    $draft = Post::factory()->draft()->create([
        'destination_id' => $destination->id,
        'location_id' => $location->id,
    ]);
    $lid = User::factory()->create();
    $lid->assignRole('lid');

    actingAs($lid)->post(route('comments.store', $draft), ['body' => 'Hoi'])
        ->assertNotFound();

    expect(Comment::count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Weergave
|--------------------------------------------------------------------------
*/

it('toont approved reacties maar verbergt pending van anderen', function () {
    $post = publishedLocationPost();
    Comment::factory()->approved()->create(['post_id' => $post->id, 'body' => 'Zichtbare reactie']);
    Comment::factory()->create(['post_id' => $post->id, 'body' => 'Verborgen pending']);

    get($post->url())
        ->assertOk()
        ->assertSee('Zichtbare reactie')
        ->assertDontSee('Verborgen pending');
});

it('toont de eigen pending reactie aan de auteur met een wacht-op-goedkeuring-label', function () {
    $post = publishedLocationPost();
    $lid = User::factory()->create();
    $lid->assignRole('lid');
    Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $lid->id,
        'body' => 'Mijn eigen pending reactie',
    ]);

    actingAs($lid)->get($post->url())
        ->assertOk()
        ->assertSee('Mijn eigen pending reactie')
        ->assertSee('wacht op goedkeuring');
});

it('verbergt afgekeurde en spam-reacties voor iedereen', function () {
    $post = publishedLocationPost();
    $lid = User::factory()->create();
    $lid->assignRole('lid');
    Comment::factory()->rejected()->create(['post_id' => $post->id, 'user_id' => $lid->id, 'body' => 'Afgekeurd bericht']);
    Comment::factory()->spam()->create(['post_id' => $post->id, 'user_id' => $lid->id, 'body' => 'Spam bericht']);

    actingAs($lid)->get($post->url())
        ->assertOk()
        ->assertDontSee('Afgekeurd bericht')
        ->assertDontSee('Spam bericht');
});

it('toont een inlog-oproep aan gasten en een reactieformulier aan ingelogde users', function () {
    $post = publishedLocationPost();

    get($post->url())
        ->assertOk()
        ->assertSee('Log in')
        ->assertDontSee('Laat een reactie achter');

    $lid = User::factory()->create();
    $lid->assignRole('lid');

    actingAs($lid)->get($post->url())
        ->assertOk()
        ->assertSee('Laat een reactie achter');
});
