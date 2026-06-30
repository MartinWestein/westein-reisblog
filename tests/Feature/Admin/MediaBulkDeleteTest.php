<?php

use App\Models\Destination;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $permissions = [
        'media.browse',
        'posts.update.any', 'posts.update.own', 'posts.create',
        'content.manage',
    ];
    foreach ($permissions as $p) {
        Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
    }

    foreach (['admin', 'editor', 'auteur', 'lid'] as $roleName) {
        Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    }

    Role::findByName('editor')->syncPermissions($permissions);
    Role::findByName('auteur')->syncPermissions(['posts.create', 'posts.update.own']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');

    $this->author = User::factory()->create();
    $this->author->assignRole('auteur');

    $this->member = User::factory()->create();
    $this->member->assignRole('lid');

    Storage::fake('public');
});

function attachAndReturn($model, string $collection, string $filename = 'test.jpg'): Media
{
    return $model->addMedia(UploadedFile::fake()->image($filename, 800, 600))
        ->toMediaCollection($collection);
}

/*
|--------------------------------------------------------------------------
| RBAC
|--------------------------------------------------------------------------
*/

it('weigert een lid', function () {
    $destination = Destination::factory()->create();
    $m1 = attachAndReturn($destination, 'hero');

    $this->actingAs($this->member)
        ->post(route('admin.media.bulk-delete'), ['ids' => [$m1->id]])
        ->assertForbidden();
});

it('weigert een auteur', function () {
    $destination = Destination::factory()->create();
    $m1 = attachAndReturn($destination, 'hero');

    $this->actingAs($this->author)
        ->post(route('admin.media.bulk-delete'), ['ids' => [$m1->id]])
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Validatie
|--------------------------------------------------------------------------
*/

it('weigert lege ids-array', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.media.index'))
        ->post(route('admin.media.bulk-delete'), ['ids' => []])
        ->assertSessionHasErrors(['ids']);
});

it('weigert te grote ids-array', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.media.index'))
        ->post(route('admin.media.bulk-delete'), ['ids' => range(1, 101)])
        ->assertSessionHasErrors(['ids']);
});

/*
|--------------------------------------------------------------------------
| Happy path
|--------------------------------------------------------------------------
*/

it('verwijdert geselecteerde media als admin', function () {
    $destination = Destination::factory()->create();
    $m1 = attachAndReturn($destination, 'hero', 'a.jpg');
    $m2 = attachAndReturn($destination, 'gallery', 'b.jpg');
    $m3 = attachAndReturn($destination, 'gallery', 'c.jpg');

    $this->actingAs($this->admin)
        ->from(route('admin.media.index'))
        ->post(route('admin.media.bulk-delete'), ['ids' => [$m1->id, $m2->id]])
        ->assertRedirect(route('admin.media.index'));

    expect(Media::find($m1->id))->toBeNull()
        ->and(Media::find($m2->id))->toBeNull()
        ->and(Media::find($m3->id))->not->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Transactie-rollback bij policy-fail
|--------------------------------------------------------------------------
*/

it('rollt alle deletes terug als één item een policy faalt', function () {
    // Realistisch policy-fail-scenario: een rol met alleen media.browse, zonder
    // update-rechten op enig content-model. Per F4-M2 niet voorzien in
    // productie-rollenmatrix, maar als unit-test op de transactie-garantie
    // (rollback bij authorize-fail mid-loop) construct-baar via custom rol.
    $limitedRole = Role::create(['name' => 'media-browser-only', 'guard_name' => 'web']);
    $limitedRole->syncPermissions(['media.browse']);

    $limitedUser = User::factory()->create();
    $limitedUser->assignRole('media-browser-only');

    $destination = Destination::factory()->create();
    $post = Post::factory()->create(['user_id' => $this->admin->id]);

    $m1 = attachAndReturn($destination, 'hero');
    $m2 = attachAndReturn($post, 'featured');

    $this->actingAs($limitedUser)
        ->from(route('admin.media.index'))
        ->post(route('admin.media.bulk-delete'), ['ids' => [$m1->id, $m2->id]])
        ->assertForbidden();

    // Beide nog aanwezig: transaction rolled back
    expect(Media::find($m1->id))->not->toBeNull()
        ->and(Media::find($m2->id))->not->toBeNull();
});
