<?php

use App\Models\Destination;
use App\Models\Location;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
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

    $this->destination = Destination::factory()->create(['name' => 'Italië']);
});

/*
|--------------------------------------------------------------------------
| Toegang — RBAC-matrix
|--------------------------------------------------------------------------
*/
it('toont de index voor een admin', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.destinations.locations.index', $this->destination))
        ->assertOk();
});

it('toont de index voor een editor', function () {
    $this->actingAs($this->editor)
        ->get(route('admin.destinations.locations.index', $this->destination))
        ->assertOk();
});

it('weigert de index voor een auteur', function () {
    $this->actingAs($this->author)
        ->get(route('admin.destinations.locations.index', $this->destination))
        ->assertForbidden();
});

it('weigert de index voor een lid', function () {
    $this->actingAs($this->member)
        ->get(route('admin.destinations.locations.index', $this->destination))
        ->assertForbidden();
});

it('stuurt een gast naar de login', function () {
    $this->get(route('admin.destinations.locations.index', $this->destination))
        ->assertRedirect(route('login'));
});

/*
|--------------------------------------------------------------------------
| Index — lijst + zoeken
|--------------------------------------------------------------------------
*/
it('toont locaties van de huidige destination op de index', function () {
    Location::factory()->for($this->destination)->create(['name' => 'Rome']);

    $this->actingAs($this->admin)
        ->get(route('admin.destinations.locations.index', $this->destination))
        ->assertOk()
        ->assertSee('Rome');
});

it('toont geen locaties van een andere destination', function () {
    $other = Destination::factory()->create(['name' => 'Schotland']);
    Location::factory()->for($other)->create(['name' => 'Edinburgh']);

    $this->actingAs($this->admin)
        ->get(route('admin.destinations.locations.index', $this->destination))
        ->assertOk()
        ->assertDontSee('Edinburgh');
});

it('filtert de index op zoekterm', function () {
    Location::factory()->for($this->destination)->create(['name' => 'Rome']);
    Location::factory()->for($this->destination)->create(['name' => 'Florence']);

    $this->actingAs($this->admin)
        ->get(route('admin.destinations.locations.index', [
            'destination' => $this->destination,
            'search' => 'Rom',
        ]))
        ->assertOk()
        ->assertSee('Rome')
        ->assertDontSee('Florence');
});

/*
|--------------------------------------------------------------------------
| Aanmaken — store
|--------------------------------------------------------------------------
*/
it('maakt een locatie aan onder de juiste destination', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.destinations.locations.store', $this->destination), [
            'name' => 'Cinque Terre',
            'latitude' => 44.1167,
            'longitude' => 9.7333,
            'country_code' => 'IT',
        ])
        ->assertRedirect();

    $location = Location::firstWhere('name', 'Cinque Terre');

    expect($location)->not->toBeNull()
        ->and($location->destination_id)->toBe($this->destination->id)
        ->and($location->slug)->toBe('cinque-terre');
});

it('redirect na aanmaken naar de edit-pagina van de locatie', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.destinations.locations.store', $this->destination), [
            'name' => 'Verona',
        ])
        ->assertRedirect(
            route('admin.destinations.locations.edit', [
                $this->destination,
                Location::firstWhere('name', 'Verona'),
            ])
        );
});

it('valideert latitude-range bij aanmaken', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.destinations.locations.store', $this->destination), [
            'name' => 'Onmogelijk',
            'latitude' => 95, // buiten -90..90
        ])
        ->assertSessionHasErrors('latitude');
});

it('valideert longitude-range bij aanmaken', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.destinations.locations.store', $this->destination), [
            'name' => 'Onmogelijk',
            'longitude' => 200, // buiten -180..180
        ])
        ->assertSessionHasErrors('longitude');
});

it('weigert aanmaken door een auteur', function () {
    $this->actingAs($this->author)
        ->post(route('admin.destinations.locations.store', $this->destination), [
            'name' => 'Verboden',
        ])
        ->assertForbidden();

    expect(Location::where('name', 'Verboden')->exists())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Scoped routing — locatie hoort bij destination uit URL
|--------------------------------------------------------------------------
*/
it('geeft 404 bij locatie benaderd via de verkeerde destination', function () {
    $other = Destination::factory()->create(['name' => 'Schotland']);
    $italianLocation = Location::factory()->for($this->destination)->create(['name' => 'Rome']);

    // Probeer de Italiaanse locatie te bewerken via de Schotse destination-URL
    $this->actingAs($this->admin)
        ->get(route('admin.destinations.locations.edit', [$other, $italianLocation]))
        ->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Bewerken — update + slug-locking
|--------------------------------------------------------------------------
*/
it('werkt een locatie bij', function () {
    $location = Location::factory()->for($this->destination)->create(['name' => 'Oud']);

    $this->actingAs($this->editor)
        ->put(route('admin.destinations.locations.update', [$this->destination, $location]), [
            'name' => 'Nieuw',
            'description' => 'Bijgewerkt.',
        ])
        ->assertRedirect(route('admin.destinations.locations.index', $this->destination));

    expect($location->fresh()->name)->toBe('Nieuw');
});

it('houdt de slug vast bij update, ook bij POST-tampering', function () {
    $location = Location::factory()->for($this->destination)->create(['name' => 'Florence']);
    $originalSlug = $location->slug;

    $this->actingAs($this->editor)
        ->put(route('admin.destinations.locations.update', [$this->destination, $location]), [
            'name' => 'Florence',
            'slug' => 'gehackte-slug',
        ]);

    expect($location->fresh()->slug)->toBe($originalSlug);
});

/*
|--------------------------------------------------------------------------
| Verwijderen — soft delete
|--------------------------------------------------------------------------
*/
it('soft-delete een locatie', function () {
    $location = Location::factory()->for($this->destination)->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.destinations.locations.destroy', [$this->destination, $location]))
        ->assertRedirect(route('admin.destinations.locations.index', $this->destination));

    expect(Location::find($location->id))->toBeNull()
        ->and(Location::withTrashed()->find($location->id))->not->toBeNull();
});

it('weigert verwijderen door een auteur', function () {
    $location = Location::factory()->for($this->destination)->create();

    $this->actingAs($this->author)
        ->delete(route('admin.destinations.locations.destroy', [$this->destination, $location]))
        ->assertForbidden();

    expect(Location::find($location->id))->not->toBeNull();
});
