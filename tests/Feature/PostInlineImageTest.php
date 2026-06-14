<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Setup
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    // PostPolicy.update zit op posts.update.own + posts.update.any
    $permissions = [
        'posts.viewAny', 'posts.view', 'posts.create',
        'posts.update.own', 'posts.update.any',
        'posts.delete.own', 'posts.delete.any',
    ];
    foreach ($permissions as $p) {
        Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
    }

    foreach (['admin', 'editor', 'auteur', 'lid'] as $roleName) {
        Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    }

    Role::findByName('editor')->syncPermissions($permissions);
    Role::findByName('auteur')->syncPermissions([
        'posts.viewAny', 'posts.view', 'posts.create',
        'posts.update.own', 'posts.delete.own',
    ]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');

    $this->author = User::factory()->create();
    $this->author->assignRole('auteur');

    $this->otherAuthor = User::factory()->create();
    $this->otherAuthor->assignRole('auteur');

    $this->member = User::factory()->create();
    $this->member->assignRole('lid');

    Storage::fake('public');
});

/*
|--------------------------------------------------------------------------
| RBAC — moet via PostPolicy.update lopen (own/any)
|--------------------------------------------------------------------------
*/

it('weigert gasten', function () {
    $post = Post::factory()->create(['user_id' => $this->admin->id]);

    $this->post(route('admin.posts.inline-images.store', $post), [
        'image' => UploadedFile::fake()->image('x.jpg'),
    ])->assertRedirect(route('login'));
});

it('weigert een lid', function () {
    $post = Post::factory()->create(['user_id' => $this->admin->id]);

    $this->actingAs($this->member)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.posts.inline-images.store', $post), [
            'image' => UploadedFile::fake()->image('x.jpg'),
        ])
        ->assertForbidden();
});

it('staat een admin toe te uploaden naar elke post', function () {
    $post = Post::factory()->create(['user_id' => $this->author->id]);

    $this->actingAs($this->admin)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.posts.inline-images.store', $post), [
            'image' => UploadedFile::fake()->image('foto.jpg', 800, 600),
            'alt' => 'Een testfoto',
        ])
        ->assertCreated();

    expect($post->fresh()->getFirstMedia('inline_images'))->not->toBeNull();
});

it('staat een editor toe te uploaden naar andermans post', function () {
    $post = Post::factory()->create(['user_id' => $this->author->id]);

    $this->actingAs($this->editor)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.posts.inline-images.store', $post), [
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ])
        ->assertCreated();
});

it('staat een auteur toe te uploaden naar eigen post', function () {
    $post = Post::factory()->create(['user_id' => $this->author->id]);

    $this->actingAs($this->author)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.posts.inline-images.store', $post), [
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ])
        ->assertCreated();

    expect($post->fresh()->getMedia('inline_images'))->toHaveCount(1);
});

it('weigert een auteur bij andermans post', function () {
    $post = Post::factory()->create(['user_id' => $this->otherAuthor->id]);

    $this->actingAs($this->author)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.posts.inline-images.store', $post), [
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ])
        ->assertForbidden();

    expect($post->fresh()->getMedia('inline_images'))->toHaveCount(0);
});

/*
|--------------------------------------------------------------------------
| Response-shape
|--------------------------------------------------------------------------
*/

it('retourneert id, url, thumb_url en alt na upload', function () {
    $post = Post::factory()->create(['user_id' => $this->admin->id]);

    $response = $this->actingAs($this->admin)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.posts.inline-images.store', $post), [
            'image' => UploadedFile::fake()->image('foto.jpg', 800, 600),
            'alt' => 'Mooie foto',
        ])
        ->assertCreated()
        ->json();

    expect($response)->toHaveKeys(['id', 'url', 'thumb_url', 'alt'])
        ->and($response['alt'])->toBe('Mooie foto');
});

it('bewaart alt als custom property op de media', function () {
    $post = Post::factory()->create(['user_id' => $this->admin->id]);

    $this->actingAs($this->admin)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.posts.inline-images.store', $post), [
            'image' => UploadedFile::fake()->image('foto.jpg'),
            'alt' => 'Met alt-tekst',
        ])
        ->assertCreated();

    expect($post->fresh()->getFirstMedia('inline_images')->getCustomProperty('alt'))
        ->toBe('Met alt-tekst');
});

it('staat upload zonder alt-tekst toe', function () {
    $post = Post::factory()->create(['user_id' => $this->admin->id]);

    $this->actingAs($this->admin)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.posts.inline-images.store', $post), [
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ])
        ->assertCreated();
});

/*
|--------------------------------------------------------------------------
| Validatie — image required, mime, size
|--------------------------------------------------------------------------
*/

it('weigert een upload zonder bestand', function () {
    $post = Post::factory()->create(['user_id' => $this->admin->id]);

    $this->actingAs($this->admin)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.posts.inline-images.store', $post), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('image');
});

it('weigert een niet-toegestaan mime-type', function () {
    $post = Post::factory()->create(['user_id' => $this->admin->id]);

    $this->actingAs($this->admin)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.posts.inline-images.store', $post), [
            'image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('image');
});

it('weigert een te lange alt-tekst', function () {
    $post = Post::factory()->create(['user_id' => $this->admin->id]);

    $this->actingAs($this->admin)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.posts.inline-images.store', $post), [
            'image' => UploadedFile::fake()->image('foto.jpg'),
            'alt' => str_repeat('a', 256),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('alt');
});
