<?php

use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Rollen + permissie die de policy nodig heeft
    Permission::firstOrCreate(['name' => 'family.manage', 'guard_name' => 'web']);

    foreach (['admin', 'editor', 'auteur', 'lid'] as $roleName) {
        Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    }

    Role::findByName('editor')->givePermissionTo('family.manage');

    // Gate::before super-admin shortcut
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');

    $this->author = User::factory()->create();
    $this->author->assignRole('auteur');

    $this->member = User::factory()->create();
    $this->member->assignRole('lid');
});

it('toont de index voor een admin', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.family-members.index'))
        ->assertOk();
});

it('staat editors toe de index te zien', function () {
    $this->actingAs($this->editor)
        ->get(route('admin.family-members.index'))
        ->assertOk();
});

it('weigert auteurs op de index', function () {
    $this->actingAs($this->author)
        ->get(route('admin.family-members.index'))
        ->assertForbidden();
});

it('weigert leden op de index', function () {
    $this->actingAs($this->member)
        ->get(route('admin.family-members.index'))
        ->assertForbidden();
});

it('stuurt gasten naar login', function () {
    $this->get(route('admin.family-members.index'))
        ->assertRedirect(route('login'));
});

it('maakt een familielid aan', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.family-members.store'), [
            'name' => 'Oma Westein',
            'role' => 'Oma',
            'bio' => 'De wereldreiziger van de familie.',
            'order' => 5,
        ])
        ->assertRedirect(route('admin.family-members.index'));

    expect(FamilyMember::where('name', 'Oma Westein')->exists())->toBeTrue();
});

it('genereert automatisch een slug', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.family-members.store'), [
            'name' => 'Opa Westein',
            'order' => 0,
        ]);

    expect(FamilyMember::where('name', 'Opa Westein')->first()->slug)
        ->toBe('opa-westein');
});

it('vereist een naam', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.family-members.store'), [
            'name' => '',
            'order' => 0,
        ])
        ->assertSessionHasErrors('name');
});

it('werkt een familielid bij', function () {
    $member = FamilyMember::create(['name' => 'Test', 'order' => 0]);

    $this->actingAs($this->admin)
        ->put(route('admin.family-members.update', $member), [
            'name' => 'Test Gewijzigd',
            'role' => 'Nieuwe rol',
            'order' => 3,
        ])
        ->assertRedirect(route('admin.family-members.index'));

    expect($member->fresh())
        ->name->toBe('Test Gewijzigd')
        ->role->toBe('Nieuwe rol')
        ->order->toBe(3);
});

it('houdt de slug stabiel bij naamswijziging', function () {
    $member = FamilyMember::create(['name' => 'Originele Naam', 'order' => 0]);
    $originalSlug = $member->slug;

    $this->actingAs($this->admin)
        ->put(route('admin.family-members.update', $member), [
            'name' => 'Compleet Andere Naam',
            'order' => 0,
        ]);

    expect($member->fresh()->slug)->toBe($originalSlug);
});

it('verwijdert een familielid', function () {
    $member = FamilyMember::create(['name' => 'Weg Ermee', 'order' => 0]);

    $this->actingAs($this->admin)
        ->delete(route('admin.family-members.destroy', $member))
        ->assertRedirect(route('admin.family-members.index'));

    expect(FamilyMember::find($member->id))->toBeNull();
});

it('koppelt een gebruiker aan een familielid', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.family-members.store'), [
            'name' => 'Gekoppeld Lid',
            'user_id' => $this->editor->id,
            'order' => 0,
        ]);

    expect(FamilyMember::where('name', 'Gekoppeld Lid')->first()->user_id)
        ->toBe($this->editor->id);
});

it('uploadt een portretfoto naar de collectie', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('portrait.jpg', 600, 600);

    $this->actingAs($this->admin)
        ->post(route('admin.family-members.store'), [
            'name' => 'Met Foto',
            'order' => 0,
            'portrait' => $file,
        ]);

    $member = FamilyMember::where('name', 'Met Foto')->first();

    expect($member->hasMedia('portrait'))->toBeTrue();
});

it('weigert een te kleine portretfoto', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('tiny.jpg', 100, 100);

    $this->actingAs($this->admin)
        ->post(route('admin.family-members.store'), [
            'name' => 'Te Klein',
            'order' => 0,
            'portrait' => $file,
        ])
        ->assertSessionHasErrors('portrait');

    expect(FamilyMember::where('name', 'Te Klein')->exists())->toBeFalse();
});

it('verwijdert een bestaand portret via de checkbox', function () {
    Storage::fake('public');

    $member = FamilyMember::create(['name' => 'Foto Weg', 'order' => 0]);
    $member->addMedia(UploadedFile::fake()->image('p.jpg', 600, 600))
        ->toMediaCollection('portrait');

    expect($member->hasMedia('portrait'))->toBeTrue();

    $this->actingAs($this->admin)
        ->put(route('admin.family-members.update', $member), [
            'name' => 'Foto Weg',
            'order' => 0,
            'remove_portrait' => '1',
        ]);

    expect($member->fresh()->hasMedia('portrait'))->toBeFalse();
});
