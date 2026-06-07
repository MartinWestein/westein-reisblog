<?php

use App\Models\Destination;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');

    Permission::firstOrCreate(['name' => 'content.manage', 'guard_name' => 'web']);
    foreach (['admin', 'editor', 'auteur', 'lid'] as $roleName) {
        Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    }
    Role::findByName('editor')->givePermissionTo('content.manage');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');

    $this->author = User::factory()->create();
    $this->author->assignRole('auteur');

    $this->destination = Destination::factory()->create();
});

/*
|--------------------------------------------------------------------------
| Upload
|--------------------------------------------------------------------------
*/
it('staat editor toe een foto te uploaden naar de gallery', function () {
    $file = UploadedFile::fake()->image('berg.jpg', 1200, 800);

    $this->actingAs($this->editor)
        ->post(route('admin.media.upload'), [
            'model_type' => 'destination',
            'model_id' => $this->destination->id,
            'collection' => 'gallery',
            'files' => [$file],
        ])
        ->assertCreated()
        ->assertJsonStructure(['media' => [['id', 'url', 'name']]]);

    expect($this->destination->fresh()->getMedia('gallery'))->toHaveCount(1);
});

it('weigert upload door een auteur', function () {
    $file = UploadedFile::fake()->image('berg.jpg', 1200, 800);

    $this->actingAs($this->author)
        ->post(route('admin.media.upload'), [
            'model_type' => 'destination',
            'model_id' => $this->destination->id,
            'collection' => 'gallery',
            'files' => [$file],
        ])
        ->assertForbidden();

    expect($this->destination->fresh()->getMedia('gallery'))->toHaveCount(0);
});

it('weigert upload met onbekend model_type', function () {
    $file = UploadedFile::fake()->image('berg.jpg', 1200, 800);

    $this->actingAs($this->editor)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.media.upload'), [
            'model_type' => 'gehackt-model',
            'model_id' => $this->destination->id,
            'collection' => 'gallery',
            'files' => [$file],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('model_type');
});

it('weigert upload van een niet-image bestand', function () {
    $file = UploadedFile::fake()->create('notes.txt', 100, 'text/plain');

    $this->actingAs($this->editor)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.media.upload'), [
            'model_type' => 'destination',
            'model_id' => $this->destination->id,
            'collection' => 'gallery',
            'files' => [$file],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('files.0');
});

/*
|--------------------------------------------------------------------------
| Reorder
|--------------------------------------------------------------------------
*/
it('herordent media via setNewOrder', function () {
    $m1 = $this->destination->addMedia(UploadedFile::fake()->image('a.jpg', 800, 600))->toMediaCollection('gallery');
    $m2 = $this->destination->addMedia(UploadedFile::fake()->image('b.jpg', 800, 600))->toMediaCollection('gallery');
    $m3 = $this->destination->addMedia(UploadedFile::fake()->image('c.jpg', 800, 600))->toMediaCollection('gallery');

    $newOrder = [$m3->id, $m1->id, $m2->id];

    $this->actingAs($this->editor)
        ->patchJson(route('admin.media.reorder'), ['ids' => $newOrder])
        ->assertOk();

    expect(Media::find($m3->id)->order_column)->toBe(1)
        ->and(Media::find($m1->id)->order_column)->toBe(2)
        ->and(Media::find($m2->id)->order_column)->toBe(3);
});

it('weigert reorder met media van twee verschillende modellen', function () {
    $other = Destination::factory()->create();

    $m1 = $this->destination->addMedia(UploadedFile::fake()->image('a.jpg', 800, 600))->toMediaCollection('gallery');
    $m2 = $other->addMedia(UploadedFile::fake()->image('b.jpg', 800, 600))->toMediaCollection('gallery');

    $this->actingAs($this->editor)
        ->patchJson(route('admin.media.reorder'), ['ids' => [$m1->id, $m2->id]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('ids');
});

it('weigert reorder door een auteur', function () {
    $m1 = $this->destination->addMedia(UploadedFile::fake()->image('a.jpg', 800, 600))->toMediaCollection('gallery');
    $m2 = $this->destination->addMedia(UploadedFile::fake()->image('b.jpg', 800, 600))->toMediaCollection('gallery');

    $this->actingAs($this->author)
        ->patchJson(route('admin.media.reorder'), ['ids' => [$m2->id, $m1->id]])
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/
it('staat editor toe een foto te verwijderen', function () {
    $media = $this->destination->addMedia(UploadedFile::fake()->image('weg.jpg', 800, 600))->toMediaCollection('gallery');

    $this->actingAs($this->editor)
        ->deleteJson(route('admin.media.destroy', $media))
        ->assertOk();

    expect(Media::find($media->id))->toBeNull();
});

it('weigert verwijderen door een auteur', function () {
    $media = $this->destination->addMedia(UploadedFile::fake()->image('blijft.jpg', 800, 600))->toMediaCollection('gallery');

    $this->actingAs($this->author)
        ->deleteJson(route('admin.media.destroy', $media))
        ->assertForbidden();

    expect(Media::find($media->id))->not->toBeNull();
});
