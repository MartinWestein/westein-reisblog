<?php

use App\Models\Destination;
use App\Models\FamilyMember;
use App\Models\Location;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $permissions = ['media.browse'];
    foreach ($permissions as $p) {
        Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
    }

    foreach (['admin', 'editor', 'auteur', 'lid'] as $roleName) {
        Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    }

    Role::findByName('editor')->syncPermissions($permissions);
    // admin via Gate::before
    // auteur + lid krijgen media.browse NIET

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

function attachMediaImage($model, string $collection, string $filename = 'test.jpg'): void
{
    $model->addMedia(UploadedFile::fake()->image($filename, 800, 600))
        ->toMediaCollection($collection);
}

/*
|--------------------------------------------------------------------------
| RBAC
|--------------------------------------------------------------------------
*/

it('redirect gasten naar login', function () {
    $this->get(route('admin.media.index'))
        ->assertRedirect(route('login'));
});

it('weigert een lid', function () {
    $this->actingAs($this->member)
        ->get(route('admin.media.index'))
        ->assertForbidden();
});

it('weigert een auteur', function () {
    $this->actingAs($this->author)
        ->get(route('admin.media.index'))
        ->assertForbidden();
});

it('staat een editor toe', function () {
    $this->actingAs($this->editor)
        ->get(route('admin.media.index'))
        ->assertOk();
});

it('staat een admin toe', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.media.index'))
        ->assertOk();
});

/*
|--------------------------------------------------------------------------
| Scope
|--------------------------------------------------------------------------
*/

it('toont alleen content-collecties', function () {
    $destination = Destination::factory()->create();
    attachMediaImage($destination, 'hero');
    attachMediaImage($destination, 'gallery');

    $post = Post::factory()->create(['user_id' => $this->admin->id]);
    attachMediaImage($post, 'featured');

    $this->actingAs($this->admin)
        ->get(route('admin.media.index'))
        ->assertOk()
        ->assertViewHas('media', fn ($paginator) => $paginator->total() === 3);
});

it('weert avatars en portraits', function () {
    $userWithAvatar = User::factory()->create();
    attachMediaImage($userWithAvatar, 'avatar');

    $familyMember = FamilyMember::factory()->create();
    attachMediaImage($familyMember, 'portrait');

    $destination = Destination::factory()->create();
    attachMediaImage($destination, 'hero', 'wel-zichtbaar.jpg');

    $this->actingAs($this->admin)
        ->get(route('admin.media.index'))
        ->assertOk()
        ->assertViewHas('media', fn ($paginator) => $paginator->total() === 1);
});

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

it('filtert op collectie', function () {
    $destination = Destination::factory()->create();
    attachMediaImage($destination, 'hero');
    attachMediaImage($destination, 'gallery');

    $this->actingAs($this->admin)
        ->get(route('admin.media.index', ['collection' => 'hero']))
        ->assertOk()
        ->assertViewHas('media', fn ($p) => $p->total() === 1);
});

it('filtert op eigenaar-type', function () {
    $destination = Destination::factory()->create();
    attachMediaImage($destination, 'hero');

    $location = Location::factory()->create();
    attachMediaImage($location, 'gallery');

    $this->actingAs($this->admin)
        ->get(route('admin.media.index', ['owner_type' => 'destination']))
        ->assertOk()
        ->assertViewHas('media', fn ($p) => $p->total() === 1);
});

it('zoekt op bestandsnaam', function () {
    $destination = Destination::factory()->create();
    attachMediaImage($destination, 'hero', 'venetie-canal.jpg');
    attachMediaImage($destination, 'gallery', 'rome-colosseum.jpg');

    $this->actingAs($this->admin)
        ->get(route('admin.media.index', ['q' => 'venetie']))
        ->assertOk()
        ->assertViewHas('media', fn ($p) => $p->total() === 1);
});

it('weigert een ongeldige collectie-filter', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.media.index', ['collection' => 'avatar']))
        ->assertSessionHasErrors(['collection']);
});

it('weigert een ongeldig eigenaar-type', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.media.index', ['owner_type' => 'family_member']))
        ->assertSessionHasErrors(['owner_type']);
});

/*
|--------------------------------------------------------------------------
| Sort
|--------------------------------------------------------------------------
*/

it('sorteert standaard op datum aflopend', function () {
    $destination = Destination::factory()->create();
    attachMediaImage($destination, 'hero', 'oude.jpg');
    sleep(1); // garandeer created_at-verschil
    attachMediaImage($destination, 'gallery', 'nieuwe.jpg');

    $this->actingAs($this->admin)
        ->get(route('admin.media.index'))
        ->assertOk()
        ->assertViewHas('media', function ($p) {
            $items = $p->items();

            return $items[0]->name === 'nieuwe' && $items[1]->name === 'oude';
        });
});

it('sorteert op naam oplopend wanneer gevraagd', function () {
    $destination = Destination::factory()->create();
    attachMediaImage($destination, 'hero', 'zebra.jpg');
    attachMediaImage($destination, 'gallery', 'alpaca.jpg');

    $this->actingAs($this->admin)
        ->get(route('admin.media.index', ['sort' => 'name', 'direction' => 'asc']))
        ->assertOk()
        ->assertViewHas('media', function ($p) {
            $items = $p->items();

            return $items[0]->name === 'alpaca' && $items[1]->name === 'zebra';
        });
});

/*
|--------------------------------------------------------------------------
| Paginatie
|--------------------------------------------------------------------------
*/

it('pagineert 25 items naar 24 + 1', function () {
    $destination = Destination::factory()->create();
    for ($i = 1; $i <= 25; $i++) {
        attachMediaImage($destination, 'gallery', "foto-{$i}.jpg");
    }

    $this->actingAs($this->admin)
        ->get(route('admin.media.index'))
        ->assertOk()
        ->assertViewHas('media', fn ($p) => $p->count() === 24 && $p->total() === 25);

    $this->actingAs($this->admin)
        ->get(route('admin.media.index', ['page' => 2]))
        ->assertOk()
        ->assertViewHas('media', fn ($p) => $p->count() === 1);
});
