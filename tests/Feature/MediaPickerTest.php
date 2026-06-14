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

/*
|--------------------------------------------------------------------------
| Setup
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    // Permissies die PostPolicy.create raakt (de browse-autorisatiecheck)
    $permissions = [
        'posts.viewAny', 'posts.view', 'posts.create',
        'posts.update.own', 'posts.delete.own',
    ];
    foreach ($permissions as $p) {
        Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
    }

    foreach (['admin', 'editor', 'auteur', 'lid'] as $roleName) {
        Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    }

    Role::findByName('editor')->syncPermissions($permissions);
    Role::findByName('auteur')->syncPermissions($permissions);
    // 'lid' krijgt geen post-rechten — PostPolicy.create faalt voor 'm

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

/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

/**
 * Hangt een fake-afbeelding aan een model in de gevraagde collectie.
 * Hergebruikt voor scope- en context-tests.
 */
function attachImage($model, string $collection, string $filename = 'test.jpg'): void
{
    $model->addMedia(UploadedFile::fake()->image($filename, 800, 600))
        ->toMediaCollection($collection);
}

/*
|--------------------------------------------------------------------------
| RBAC
|--------------------------------------------------------------------------
*/

it('weigert gasten op de media-picker', function () {
    $this->getJson(route('admin.media-picker.index'))
        ->assertUnauthorized();
});

it('weigert een lid op de media-picker', function () {
    $this->actingAs($this->member)
        ->getJson(route('admin.media-picker.index'))
        ->assertForbidden();
});

it('staat een admin toe op de media-picker', function () {
    $this->actingAs($this->admin)
        ->getJson(route('admin.media-picker.index'))
        ->assertOk();
});

it('staat een editor toe op de media-picker', function () {
    $this->actingAs($this->editor)
        ->getJson(route('admin.media-picker.index'))
        ->assertOk();
});

it('staat een auteur toe op de media-picker', function () {
    $this->actingAs($this->author)
        ->getJson(route('admin.media-picker.index'))
        ->assertOk();
});

/*
|--------------------------------------------------------------------------
| Browse-scope — alleen content-collecties
|--------------------------------------------------------------------------
*/

it('toont media uit de toegestane collecties', function () {
    $destination = Destination::factory()->create();
    attachImage($destination, 'hero');
    attachImage($destination, 'gallery');

    $location = Location::factory()->create();
    attachImage($location, 'gallery');

    $post = Post::factory()->create(['user_id' => $this->admin->id]);
    attachImage($post, 'featured');
    attachImage($post, 'inline_images');

    $response = $this->actingAs($this->admin)
        ->getJson(route('admin.media-picker.index'))
        ->assertOk()
        ->json();

    expect($response['items'])->toHaveCount(5);
});

it('weert avatars en family-portraits uit de picker', function () {
    $userWithAvatar = User::factory()->create();
    attachImage($userWithAvatar, 'avatar');

    $familyMember = FamilyMember::factory()->create();
    attachImage($familyMember, 'portrait');

    // Plus één content-foto die wél binnen scope valt
    $destination = Destination::factory()->create();
    attachImage($destination, 'hero', 'wel-zichtbaar.jpg');

    $response = $this->actingAs($this->admin)
        ->getJson(route('admin.media-picker.index'))
        ->assertOk()
        ->json();

    expect($response['items'])->toHaveCount(1)
        ->and($response['items'][0]['context'])->toContain('Bestemming');
});

it('filtert op een specifieke collectie', function () {
    $destination = Destination::factory()->create();
    attachImage($destination, 'hero');
    attachImage($destination, 'gallery');

    $response = $this->actingAs($this->admin)
        ->getJson(route('admin.media-picker.index', ['collection' => 'hero']))
        ->assertOk()
        ->json();

    expect($response['items'])->toHaveCount(1);
});

it('weigert een onbekende collectie-filter', function () {
    $this->actingAs($this->admin)
        ->getJson(route('admin.media-picker.index', ['collection' => 'avatar']))
        ->assertStatus(422);
});

/*
|--------------------------------------------------------------------------
| Zoeken + context-labels + paginatie
|--------------------------------------------------------------------------
*/

it('zoekt op bestandsnaam', function () {
    $destination = Destination::factory()->create();
    attachImage($destination, 'hero', 'venetie-canal.jpg');
    attachImage($destination, 'gallery', 'rome-colosseum.jpg');

    $response = $this->actingAs($this->admin)
        ->getJson(route('admin.media-picker.index', ['search' => 'venetie']))
        ->assertOk()
        ->json();

    expect($response['items'])->toHaveCount(1);
});

it('toont een context-label per item', function () {
    $destination = Destination::factory()->create(['name' => 'Italië']);
    attachImage($destination, 'hero');

    $location = Location::factory()->create([
        'destination_id' => $destination->id,
        'name' => 'Rome',
    ]);
    attachImage($location, 'gallery');

    $items = collect($this->actingAs($this->admin)
        ->getJson(route('admin.media-picker.index'))
        ->json('items'));

    expect($items->pluck('context'))
        ->toContain('Bestemming: Italië')
        ->toContain('Locatie: Italië → Rome');
});

it('pagineert met een cursor', function () {
    // 30 items = ruim boven de per-page (24)
    $destination = Destination::factory()->create();
    for ($i = 1; $i <= 30; $i++) {
        attachImage($destination, 'gallery', "foto-{$i}.jpg");
    }

    $first = $this->actingAs($this->admin)
        ->getJson(route('admin.media-picker.index'))
        ->assertOk()
        ->json();

    expect($first['items'])->toHaveCount(24)
        ->and($first['next_cursor'])->not->toBeNull();

    $second = $this->actingAs($this->admin)
        ->getJson(route('admin.media-picker.index', ['cursor' => $first['next_cursor']]))
        ->assertOk()
        ->json();

    expect($second['items'])->toHaveCount(6)
        ->and($second['next_cursor'])->toBeNull();
});

it('geeft een lege lijst zonder media', function () {
    $response = $this->actingAs($this->admin)
        ->getJson(route('admin.media-picker.index'))
        ->assertOk()
        ->json();

    expect($response['items'])->toBe([])
        ->and($response['next_cursor'])->toBeNull();
});
