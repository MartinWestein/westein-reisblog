<?php

use App\Models\Destination;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Permissie die de policy nodig heeft + alle rollen
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

    $this->member = User::factory()->create();
    $this->member->assignRole('lid');
});

/*
|--------------------------------------------------------------------------
| Toegang — RBAC-matrix
|--------------------------------------------------------------------------
*/
it('toont de index voor een admin', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.destinations.index'))
        ->assertOk();
});

it('toont de index voor een editor (heeft content.manage)', function () {
    $this->actingAs($this->editor)
        ->get(route('admin.destinations.index'))
        ->assertOk();
});

it('weigert de index voor een auteur', function () {
    $this->actingAs($this->author)
        ->get(route('admin.destinations.index'))
        ->assertForbidden();
});

it('weigert de index voor een lid', function () {
    $this->actingAs($this->member)
        ->get(route('admin.destinations.index'))
        ->assertForbidden();
});

it('stuurt een gast naar de login', function () {
    $this->get(route('admin.destinations.index'))
        ->assertRedirect(route('login'));
});

/*
|--------------------------------------------------------------------------
| Index — lijst + zoeken
|--------------------------------------------------------------------------
*/
it('toont bestaande bestemmingen op de index', function () {
    $destination = Destination::factory()->create(['name' => 'Italië']);

    $this->actingAs($this->admin)
        ->get(route('admin.destinations.index'))
        ->assertOk()
        ->assertSee('Italië');
});

it('filtert de index op zoekterm', function () {
    Destination::factory()->create(['name' => 'Schotland']);
    Destination::factory()->create(['name' => 'Portugal']);

    $this->actingAs($this->admin)
        ->get(route('admin.destinations.index', ['search' => 'Schot']))
        ->assertOk()
        ->assertSee('Schotland')
        ->assertDontSee('Portugal');
});

/*
|--------------------------------------------------------------------------
| Aanmaken — store
|--------------------------------------------------------------------------
*/
it('maakt een bestemming aan en genereert de slug', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.destinations.store'), [
            'name' => 'Noord-Italië',
            'description' => 'De bergachtige noordkant.',
            'country_code' => 'IT',
        ])
        ->assertRedirect();

    $destination = Destination::firstWhere('name', 'Noord-Italië');

    expect($destination)->not->toBeNull()
        ->and($destination->slug)->toBe('noord-italie');
});

it('redirect na aanmaken naar de edit-pagina', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.destinations.store'), [
            'name' => 'Kroatië',
        ])
        ->assertRedirect(
            route('admin.destinations.edit', Destination::firstWhere('name', 'Kroatië'))
        );
});

it('uppercaset de landcode bij aanmaken', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.destinations.store'), [
            'name' => 'Spanje',
            'country_code' => 'es',
        ]);

    expect(Destination::firstWhere('name', 'Spanje')->country_code)->toBe('ES');
});

it('vereist een naam bij aanmaken', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.destinations.store'), [
            'name' => '',
        ])
        ->assertSessionHasErrors('name');
});

it('weigert aanmaken door een auteur', function () {
    $this->actingAs($this->author)
        ->post(route('admin.destinations.store'), [
            'name' => 'Verboden',
        ])
        ->assertForbidden();

    expect(Destination::where('name', 'Verboden')->exists())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Bewerken — update + slug-locking
|--------------------------------------------------------------------------
*/
it('werkt een bestemming bij', function () {
    $destination = Destination::factory()->create(['name' => 'Oud']);

    $this->actingAs($this->editor)
        ->put(route('admin.destinations.update', $destination), [
            'name' => 'Nieuw',
            'description' => 'Bijgewerkt.',
        ])
        ->assertRedirect(route('admin.destinations.index'));

    expect($destination->fresh()->name)->toBe('Nieuw');
});

it('houdt de slug vast bij update, ook bij POST-tampering', function () {
    $destination = Destination::factory()->create(['name' => 'Frankrijk']);
    $originalSlug = $destination->slug;

    $this->actingAs($this->editor)
        ->put(route('admin.destinations.update', $destination), [
            'name' => 'Frankrijk',
            'slug' => 'gehackte-slug', // moet genegeerd worden
        ]);

    expect($destination->fresh()->slug)->toBe($originalSlug);
});

it('weigert bewerken door een lid', function () {
    $destination = Destination::factory()->create();

    $this->actingAs($this->member)
        ->put(route('admin.destinations.update', $destination), [
            'name' => 'Poging',
        ])
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Verwijderen — soft delete
|--------------------------------------------------------------------------
*/
it('soft-delete een bestemming', function () {
    $destination = Destination::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.destinations.destroy', $destination))
        ->assertRedirect(route('admin.destinations.index'));

    expect(Destination::find($destination->id))->toBeNull()
        ->and(Destination::withTrashed()->find($destination->id))->not->toBeNull();
});

it('weigert verwijderen door een auteur', function () {
    $destination = Destination::factory()->create();

    $this->actingAs($this->author)
        ->delete(route('admin.destinations.destroy', $destination))
        ->assertForbidden();

    expect(Destination::find($destination->id))->not->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Uitlichten — is_featured toggle (5.1.b-i)
|--------------------------------------------------------------------------
*/

it('maakt een bestemming aan met is_featured aangevinkt', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.destinations.store'), [
            'name' => 'Beste bestemming',
            'is_featured' => '1',
        ])
        ->assertRedirect();

    expect(Destination::firstWhere('name', 'Beste bestemming')->is_featured)->toBeTrue();
});

it('slaat is_featured standaard op als false bij aanmaken zonder toggle', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.destinations.store'), [
            'name' => 'Gewoon land',
        ])
        ->assertRedirect();

    expect(Destination::firstWhere('name', 'Gewoon land')->is_featured)->toBeFalse();
});

it('zet is_featured aan via update', function () {
    $destination = Destination::factory()->create(['is_featured' => false]);

    $this->actingAs($this->editor)
        ->put(route('admin.destinations.update', $destination), [
            'name' => $destination->name,
            'is_featured' => '1',
        ]);

    expect($destination->fresh()->is_featured)->toBeTrue();
});

it('zet is_featured weer uit via update wanneer checkbox niet aangevinkt is', function () {
    $destination = Destination::factory()->create(['is_featured' => true]);

    $this->actingAs($this->editor)
        ->put(route('admin.destinations.update', $destination), [
            'name' => $destination->name,
            // is_featured bewust weggelaten — simuleert een uitgevinkte checkbox
        ]);

    expect($destination->fresh()->is_featured)->toBeFalse();
});

it('toont een uitgelicht-badge op de index bij is_featured-bestemmingen', function () {
    Destination::factory()->create(['name' => 'Beste bestemming', 'is_featured' => true]);
    Destination::factory()->create(['name' => 'Middelmatig', 'is_featured' => false]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.destinations.index'))
        ->assertOk()
        ->assertSee('Beste bestemming')
        ->assertSee('Middelmatig');

    // Badge komt precies één keer voor — alleen bij de featured destination
    expect(substr_count($response->getContent(), 'Uitgelicht'))->toBe(1);
});

it('scopeFeatured pickt alleen bestemmingen met is_featured=true', function () {
    Destination::factory()->create(['name' => 'A', 'is_featured' => true]);
    Destination::factory()->create(['name' => 'B', 'is_featured' => false]);
    Destination::factory()->create(['name' => 'C', 'is_featured' => true]);

    $featured = Destination::featured()->pluck('name')->toArray();

    expect($featured)->toHaveCount(2)
        ->and($featured)->toContain('A', 'C')
        ->and($featured)->not->toContain('B');
});
