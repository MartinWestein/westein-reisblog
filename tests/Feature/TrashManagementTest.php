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

test('restore herstelt een post zonder soft-deleted parents', function () {
    $destination = \App\Models\Destination::factory()->create();
    $post = \App\Models\Post::factory()->for($destination)->create();
    $post->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.restore', ['type' => 'post', 'id' => $post->id]))
        ->assertRedirect(route('admin.trash.index'));

    expect($post->fresh()->trashed())->toBeFalse();
});

test('restore van post cascadeert omhoog naar soft-deleted destination', function () {
    $destination = \App\Models\Destination::factory()->create();
    $post = \App\Models\Post::factory()->for($destination)->create();
    $post->delete();
    $destination->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.restore', ['type' => 'post', 'id' => $post->id]))
        ->assertRedirect(route('admin.trash.index'))
        ->assertSessionHas('success');

    expect($post->fresh()->trashed())->toBeFalse();
    expect($destination->fresh()->trashed())->toBeFalse();
});

test('restore van post cascadeert door location naar destination', function () {
    $destination = \App\Models\Destination::factory()->create();
    $location = \App\Models\Location::factory()->for($destination)->create();
    $post = \App\Models\Post::factory()
        ->for($destination)
        ->for($location)
        ->create();

    $post->delete();
    $location->delete();
    $destination->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.restore', ['type' => 'post', 'id' => $post->id]))
        ->assertRedirect(route('admin.trash.index'));

    expect($post->fresh()->trashed())->toBeFalse();
    expect($location->fresh()->trashed())->toBeFalse();
    expect($destination->fresh()->trashed())->toBeFalse();
});

test('restore van location cascadeert naar soft-deleted destination', function () {
    $destination = \App\Models\Destination::factory()->create();
    $location = \App\Models\Location::factory()->for($destination)->create();
    $location->delete();
    $destination->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.restore', ['type' => 'location', 'id' => $location->id]))
        ->assertRedirect(route('admin.trash.index'));

    expect($location->fresh()->trashed())->toBeFalse();
    expect($destination->fresh()->trashed())->toBeFalse();
});

test('restore van location laat levende destination met rust', function () {
    $destination = \App\Models\Destination::factory()->create();
    $location = \App\Models\Location::factory()->for($destination)->create();
    $location->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.restore', ['type' => 'location', 'id' => $location->id]))
        ->assertRedirect(route('admin.trash.index'));

    expect($location->fresh()->trashed())->toBeFalse();
    expect($destination->fresh()->trashed())->toBeFalse();
});

test('restore werkt voor destination, route en page', function (string $type, string $factory) {
    $model = $factory::factory()->create();
    $model->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.restore', ['type' => $type, 'id' => $model->id]))
        ->assertRedirect(route('admin.trash.index'));

    expect($model->fresh()->trashed())->toBeFalse();
})->with([
    ['destination', \App\Models\Destination::class],
    ['route', \App\Models\Route::class],
    ['page', \App\Models\Page::class],
]);

test('restore van niet-trashed item returnt 404', function () {
    $post = \App\Models\Post::factory()->create();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.restore', ['type' => 'post', 'id' => $post->id]))
        ->assertNotFound();
});

test('restore van niet-bestaand id returnt 404', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.restore', ['type' => 'post', 'id' => 99999]))
        ->assertNotFound();
});

test('bad type in restore-URL botst tegen route-constraint 404', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.restore', ['type' => 'hackattempt', 'id' => 1]))
        ->assertNotFound();
});

test('auteur krijgt 403 op restore-endpoint', function () {
    $post = \App\Models\Post::factory()->create();
    $post->delete();

    $user = User::factory()->create();
    $user->assignRole('auteur');

    $this->actingAs($user)
        ->post(route('admin.trash.restore', ['type' => 'post', 'id' => $post->id]))
        ->assertForbidden();

    expect($post->fresh()->trashed())->toBeTrue();
});

test('lid krijgt 403 op restore-endpoint', function () {
    $post = \App\Models\Post::factory()->create();
    $post->delete();

    $user = User::factory()->create();
    $user->assignRole('lid');

    $this->actingAs($user)
        ->post(route('admin.trash.restore', ['type' => 'post', 'id' => $post->id]))
        ->assertForbidden();
});

test('force-delete verwijdert een post definitief', function () {
    $post = \App\Models\Post::factory()->create();
    $post->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->delete(route('admin.trash.force-delete', ['type' => 'post', 'id' => $post->id]))
        ->assertRedirect(route('admin.trash.index'))
        ->assertSessionHas('success');

    expect(\App\Models\Post::withTrashed()->find($post->id))->toBeNull();
});

test('force-delete werkt voor location, route en page zonder blokkade', function (string $type, string $factory) {
    $model = $factory::factory()->create();
    $model->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->delete(route('admin.trash.force-delete', ['type' => $type, 'id' => $model->id]))
        ->assertRedirect(route('admin.trash.index'));

    expect($factory::withTrashed()->find($model->id))->toBeNull();
})->with([
    ['location', \App\Models\Location::class],
    ['route', \App\Models\Route::class],
    ['page', \App\Models\Page::class],
]);

test('force-delete van destination met levende location wordt geblokkeerd', function () {
    $destination = \App\Models\Destination::factory()->create();
    \App\Models\Location::factory()->for($destination)->create();
    $destination->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->delete(route('admin.trash.force-delete', ['type' => 'destination', 'id' => $destination->id]))
        ->assertRedirect(route('admin.trash.index'))
        ->assertSessionHas('error');

    expect(\App\Models\Destination::withTrashed()->find($destination->id))->not->toBeNull();
});

test('force-delete van destination met soft-deleted location wordt geblokkeerd', function () {
    $destination = \App\Models\Destination::factory()->create();
    $location = \App\Models\Location::factory()->for($destination)->create();
    $location->delete();
    $destination->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->delete(route('admin.trash.force-delete', ['type' => 'destination', 'id' => $destination->id]))
        ->assertRedirect(route('admin.trash.index'))
        ->assertSessionHas('error');

    expect(\App\Models\Destination::withTrashed()->find($destination->id))->not->toBeNull();
});

test('force-delete van kinderloze destination gaat door', function () {
    $destination = \App\Models\Destination::factory()->create();
    $destination->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->delete(route('admin.trash.force-delete', ['type' => 'destination', 'id' => $destination->id]))
        ->assertRedirect(route('admin.trash.index'))
        ->assertSessionHas('success');

    expect(\App\Models\Destination::withTrashed()->find($destination->id))->toBeNull();
});

test('force-delete van niet-trashed item returnt 404', function () {
    $post = \App\Models\Post::factory()->create();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->delete(route('admin.trash.force-delete', ['type' => 'post', 'id' => $post->id]))
        ->assertNotFound();

    expect(\App\Models\Post::find($post->id))->not->toBeNull();
});

test('auteur krijgt 403 op force-delete-endpoint', function () {
    $post = \App\Models\Post::factory()->create();
    $post->delete();

    $user = User::factory()->create();
    $user->assignRole('auteur');

    $this->actingAs($user)
        ->delete(route('admin.trash.force-delete', ['type' => 'post', 'id' => $post->id]))
        ->assertForbidden();

    expect(\App\Models\Post::withTrashed()->find($post->id))->not->toBeNull();
});

test('lid krijgt 403 op force-delete-endpoint', function () {
    $post = \App\Models\Post::factory()->create();
    $post->delete();

    $user = User::factory()->create();
    $user->assignRole('lid');

    $this->actingAs($user)
        ->delete(route('admin.trash.force-delete', ['type' => 'post', 'id' => $post->id]))
        ->assertForbidden();
});

test('geblokkeerde destination in tabel toont disabled delete-knop', function () {
    $destination = \App\Models\Destination::factory()->create();
    \App\Models\Location::factory()->for($destination)->create();
    $destination->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.trash.index'))
        ->assertOk()
        ->assertSee('hangt hieronder')
        ->assertSee('disabled', false);
});

test('bulk-restore herstelt heterogene selectie in één transactie', function () {
    $post = \App\Models\Post::factory()->create();
    $destination = \App\Models\Destination::factory()->create();
    $page = \App\Models\Page::factory()->create();

    $post->delete();
    $destination->delete();
    $page->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.bulk-restore'), [
            'items' => [
                ['type' => 'post', 'id' => $post->id],
                ['type' => 'destination', 'id' => $destination->id],
                ['type' => 'page', 'id' => $page->id],
            ],
        ])
        ->assertRedirect(route('admin.trash.index'))
        ->assertSessionHas('success');

    expect($post->fresh()->trashed())->toBeFalse();
    expect($destination->fresh()->trashed())->toBeFalse();
    expect($page->fresh()->trashed())->toBeFalse();
});

test('bulk-restore cascadeert ancestors dedup per unieke parent', function () {
    $destination = \App\Models\Destination::factory()->create();
    $location = \App\Models\Location::factory()->for($destination)->create();
    $post1 = \App\Models\Post::factory()->for($destination)->for($location)->create();
    $post2 = \App\Models\Post::factory()->for($destination)->for($location)->create();

    $post1->delete();
    $post2->delete();
    $location->delete();
    $destination->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.bulk-restore'), [
            'items' => [
                ['type' => 'post', 'id' => $post1->id],
                ['type' => 'post', 'id' => $post2->id],
            ],
        ])
        ->assertRedirect(route('admin.trash.index'));

    expect($post1->fresh()->trashed())->toBeFalse();
    expect($post2->fresh()->trashed())->toBeFalse();
    expect($location->fresh()->trashed())->toBeFalse();
    expect($destination->fresh()->trashed())->toBeFalse();
});

test('bulk-restore van niet-bestaand item wordt silent overgeslagen', function () {
    $post = \App\Models\Post::factory()->create();
    $post->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.bulk-restore'), [
            'items' => [
                ['type' => 'post', 'id' => $post->id],
                ['type' => 'post', 'id' => 99999],
            ],
        ])
        ->assertRedirect(route('admin.trash.index'))
        ->assertSessionHas('success');

    expect($post->fresh()->trashed())->toBeFalse();
});

test('bulk-restore weigert lege items-array', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.bulk-restore'), ['items' => []])
        ->assertSessionHasErrors('items');
});

test('bulk-restore weigert meer dan 100 items', function () {
    $items = array_map(fn ($i) => ['type' => 'post', 'id' => $i], range(1, 101));

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.bulk-restore'), ['items' => $items])
        ->assertSessionHasErrors('items');
});

test('bulk-restore weigert ongeldige types', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.bulk-restore'), [
            'items' => [['type' => 'hackattempt', 'id' => 1]],
        ])
        ->assertSessionHasErrors('items.0.type');
});

test('bulk-restore accepteert JSON-string als items-payload', function () {
    $post = \App\Models\Post::factory()->create();
    $post->delete();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.trash.bulk-restore'), [
            'items' => json_encode([['type' => 'post', 'id' => $post->id]]),
        ])
        ->assertRedirect(route('admin.trash.index'));

    expect($post->fresh()->trashed())->toBeFalse();
});

test('auteur krijgt 403 op bulk-restore-endpoint', function () {
    $post = \App\Models\Post::factory()->create();
    $post->delete();

    $user = User::factory()->create();
    $user->assignRole('auteur');

    $this->actingAs($user)
        ->post(route('admin.trash.bulk-restore'), [
            'items' => [['type' => 'post', 'id' => $post->id]],
        ])
        ->assertForbidden();

    expect($post->fresh()->trashed())->toBeTrue();
});

test('lid krijgt 403 op bulk-restore-endpoint', function () {
    $user = User::factory()->create();
    $user->assignRole('lid');

    $this->actingAs($user)
        ->post(route('admin.trash.bulk-restore'), [
            'items' => [['type' => 'post', 'id' => 1]],
        ])
        ->assertForbidden();
});
