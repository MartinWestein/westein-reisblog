<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'users.manage', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'auteur', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'lid', 'guard_name' => 'web']);
});

test('admin heeft toegang tot de gebruikersbeheer index', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertOk();
});

test('editor krijgt 403 op de gebruikersbeheer index', function () {
    $user = User::factory()->create();
    $user->assignRole('editor');

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('auteur krijgt 403 op de gebruikersbeheer index', function () {
    $user = User::factory()->create();
    $user->assignRole('auteur');

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('lid krijgt 403 op de gebruikersbeheer index', function () {
    $user = User::factory()->create();
    $user->assignRole('lid');

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('guest wordt naar login geredirect vanaf de gebruikersbeheer index', function () {
    $this->get(route('admin.users.index'))
        ->assertRedirect(route('login'));
});

test('non-admin krijgt 403 op schrijf-endpoints van gebruikersbeheer', function () {
    $user = User::factory()->create();
    $user->assignRole('editor');

    $this->actingAs($user)
        ->post(route('admin.users.store'), [])
        ->assertForbidden();
});

test('zoekt op naam en email', function () {
    $admin = User::factory()->create(['name' => 'Beheerder', 'email' => 'admin@test.local']);
    $admin->assignRole('admin');

    User::factory()->create(['name' => 'Aaltje Janssen', 'email' => 'aaltje@voorbeeld.nl']);
    User::factory()->create(['name' => 'Bertus de Vries', 'email' => 'bertus@ander.nl']);
    User::factory()->create(['name' => 'Chris Pietersen', 'email' => 'chris@voorbeeld.nl']);

    // Zoek op deel van naam
    $response = $this->actingAs($admin)
        ->get(route('admin.users.index', ['search' => 'Aaltje']));

    $response->assertOk()
        ->assertSee('Aaltje Janssen')
        ->assertDontSee('Bertus de Vries')
        ->assertDontSee('Chris Pietersen');

    // Zoek op deel van email
    $response = $this->actingAs($admin)
        ->get(route('admin.users.index', ['search' => 'ander.nl']));

    $response->assertOk()
        ->assertSee('Bertus de Vries')
        ->assertDontSee('Aaltje Janssen')
        ->assertDontSee('Chris Pietersen');
});

test('filtert op rol', function () {
    $admin = User::factory()->create(['email' => 'admin@rol-test.local']);
    $admin->assignRole('admin');

    User::factory()->create(['email' => 'editor@rol-test.local'])->assignRole('editor');
    User::factory()->create(['email' => 'auteur@rol-test.local'])->assignRole('auteur');

    $response = $this->actingAs($admin)
        ->get(route('admin.users.index', ['role' => 'editor']));

    $response->assertOk()
        ->assertSee('editor@rol-test.local')
        ->assertDontSee('auteur@rol-test.local')
        ->assertDontSee('admin@rol-test.local');
});

test('filtert op status actief en gedeactiveerd', function () {
    $admin = User::factory()->create(['email' => 'admin@status-test.local']);
    $admin->assignRole('admin');

    User::factory()->create(['email' => 'actief@status-test.local'])->assignRole('lid');
    User::factory()->create([
        'email' => 'gedeactiveerd@status-test.local',
        'deactivated_at' => now(),
    ])->assignRole('lid');

    // Filter: alleen actief
    $response = $this->actingAs($admin)
        ->get(route('admin.users.index', ['status' => 'active']));

    $response->assertOk()
        ->assertSee('actief@status-test.local')
        ->assertDontSee('gedeactiveerd@status-test.local');

    // Filter: alleen gedeactiveerd
    $response = $this->actingAs($admin)
        ->get(route('admin.users.index', ['status' => 'deactivated']));

    $response->assertOk()
        ->assertSee('gedeactiveerd@status-test.local')
        ->assertDontSee('actief@status-test.local');
});

test('sorteert op naam oplopend', function () {
    $admin = User::factory()->create(['name' => 'Zeger Admin']);
    $admin->assignRole('admin');

    User::factory()->create(['name' => 'Aaltje Eerst']);
    User::factory()->create(['name' => 'Mieke Midden']);

    $response = $this->actingAs($admin)
        ->get(route('admin.users.index', ['sort' => 'name', 'direction' => 'asc']));

    $response->assertOk();

    $names = $response->viewData('users')->pluck('name')->all();
    expect($names)->toBe(['Aaltje Eerst', 'Mieke Midden', 'Zeger Admin']);
});

test('valt terug op default sort bij onbekende sort-kolom', function () {
    $admin = User::factory()->create(['name' => 'Beheerder Chef']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)
        ->get(route('admin.users.index', ['sort' => 'password_hash']));

    // Mag niet crashen; toont gewoon de index
    $response->assertOk();
});

test('valt terug op alle-rollen bij onbekende rol-filter', function () {
    $admin = User::factory()->create(['name' => 'Beheerder Chef']);
    $admin->assignRole('admin');

    User::factory()->create(['name' => 'Redacteur Els'])->assignRole('editor');
    User::factory()->create(['name' => 'Auteur Piet'])->assignRole('auteur');

    $response = $this->actingAs($admin)
        ->get(route('admin.users.index', ['role' => 'niet-bestaande-rol']));

    // Onbekend filter -> genormaliseerd naar 'all' -> alle users zichtbaar
    $response->assertOk()
        ->assertSee('Redacteur Els')
        ->assertSee('Auteur Piet');
});

test('pagineert op 25 per pagina', function () {
    $admin = User::factory()->create(['name' => 'Beheerder Chef']);
    $admin->assignRole('admin');

    // 30 users aanmaken (samen met admin = 31 totaal, 25 op pagina 1 + 6 op pagina 2)
    User::factory()->count(30)->create();

    $response = $this->actingAs($admin)
        ->get(route('admin.users.index'));

    $response->assertOk();

    // Verifieer paginator: 25 op pagina 1 verwacht (default per_page in controller)
    // We tellen via de view-data — 't blijkt makkelijker dan de HTML door te lopen
    $users = $response->viewData('users');
    expect($users->perPage())->toBe(25);
    expect($users->total())->toBe(31);
    expect($users->count())->toBe(25);
});
