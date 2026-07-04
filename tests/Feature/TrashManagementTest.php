<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'trash.manage', 'guard_name' => 'web']);

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
    $editor->givePermissionTo('trash.manage');

    Role::firstOrCreate(['name' => 'auteur', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'lid', 'guard_name' => 'web']);
});

test('admin heeft toegang tot de prullenbak', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get(route('admin.trash.index'))
        ->assertOk();
});

test('editor heeft toegang tot de prullenbak', function () {
    $user = User::factory()->create();
    $user->assignRole('editor');

    $this->actingAs($user)
        ->get(route('admin.trash.index'))
        ->assertOk();
});

test('auteur krijgt 403 op de prullenbak', function () {
    $user = User::factory()->create();
    $user->assignRole('auteur');

    $this->actingAs($user)
        ->get(route('admin.trash.index'))
        ->assertForbidden();
});

test('lid krijgt 403 op de prullenbak', function () {
    $user = User::factory()->create();
    $user->assignRole('lid');

    $this->actingAs($user)
        ->get(route('admin.trash.index'))
        ->assertForbidden();
});

test('guest wordt naar login geredirect', function () {
    $this->get(route('admin.trash.index'))
        ->assertRedirect(route('login'));
});

test('toont soft-deleted items uit alle vijf types', function () {
    $post = \App\Models\Post::factory()->create();
    $destination = \App\Models\Destination::factory()->create();
    $location = \App\Models\Location::factory()->for($destination)->create();
    $route = \App\Models\Route::factory()->create();
    $page = \App\Models\Page::factory()->create();

    $post->delete();
    $destination->delete();
    $location->delete();
    $route->delete();
    $page->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.trash.index'))
        ->assertOk()
        ->assertSee($post->title)
        ->assertSee($destination->name)
        ->assertSee($location->name)
        ->assertSee($route->name)
        ->assertSee($page->title);
});

test('type-filter toont alleen items van gekozen type', function () {
    $post = \App\Models\Post::factory()->create();
    $destination = \App\Models\Destination::factory()->create();
    $post->delete();
    $destination->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.trash.index', ['type' => 'post']))
        ->assertOk()
        ->assertSee($post->title)
        ->assertDontSee($destination->name);
});

test('bad type-filter valt silent terug op alle types', function () {
    $post = \App\Models\Post::factory()->create();
    $post->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.trash.index', ['type' => 'hackattempt']))
        ->assertOk()
        ->assertSee($post->title);
});

test('sorteert op deleted_at descending', function () {
    $oldPost = \App\Models\Post::factory()->create(['title' => 'Oud verhaal']);
    $newPost = \App\Models\Post::factory()->create(['title' => 'Nieuw verhaal']);

    $oldPost->delete();
    $this->travel(1)->hour();
    $newPost->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $content = $this->actingAs($admin)
        ->get(route('admin.trash.index'))
        ->getContent();

    expect(strpos($content, 'Nieuw verhaal'))
        ->toBeLessThan(strpos($content, 'Oud verhaal'));
});

test('toont empty state met type-hint als type-filter geen resultaten heeft', function () {
    $post = \App\Models\Post::factory()->create();
    $post->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.trash.index', ['type' => 'page']))
        ->assertOk()
        ->assertSee('Geen verwijderde items van dit type.');
});
