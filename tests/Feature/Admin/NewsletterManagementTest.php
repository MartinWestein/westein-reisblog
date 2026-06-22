<?php

use App\Models\Newsletter;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['admin', 'editor', 'auteur', 'lid'] as $role) {
        Role::findOrCreate($role);
    }
    $perm = Permission::findOrCreate('newsletters.manage');
    Role::findByName('editor')->givePermissionTo($perm);

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('weigert gasten op de index', function () {
    $this->get(route('admin.newsletters.index'))
        ->assertRedirect(route('login'));
});

it('weigert lid op de index', function () {
    $lid = User::factory()->create();
    $lid->assignRole('lid');

    $this->actingAs($lid)
        ->get(route('admin.newsletters.index'))
        ->assertForbidden();
});

it('weigert auteur op de index', function () {
    $auteur = User::factory()->create();
    $auteur->assignRole('auteur');

    $this->actingAs($auteur)
        ->get(route('admin.newsletters.index'))
        ->assertForbidden();
});

it('staat editor toe op de index', function () {
    $this->actingAs($this->editor)
        ->get(route('admin.newsletters.index'))
        ->assertOk()
        ->assertSee(__('Nieuwsbrieven'));
});

it('staat admin toe op de index', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.newsletters.index'))
        ->assertOk();
});

it('toont de index met counts per status', function () {
    Newsletter::factory()->count(2)->create();
    Newsletter::factory()->sending()->count(1)->create();
    Newsletter::factory()->sent(50)->count(3)->create();

    $response = $this->actingAs($this->editor)
        ->get(route('admin.newsletters.index'))
        ->assertOk();

    // Counts in select-options (2 draft + 1 sending + 3 sent = 6 totaal)
    $response->assertSee('(6)')
        ->assertSee('(2)')
        ->assertSee('(1)')
        ->assertSee('(3)');
});

it('filtert op status draft', function () {
    Newsletter::factory()->count(2)->create(['subject' => 'Draft brief']);
    Newsletter::factory()->sent(50)->count(3)->create(['subject' => 'Verzonden brief']);

    $this->actingAs($this->editor)
        ->get(route('admin.newsletters.index', ['status' => 'draft']))
        ->assertOk()
        ->assertSee('Draft brief')
        ->assertDontSee('Verzonden brief');
});

it('filtert op status sent', function () {
    Newsletter::factory()->count(2)->create(['subject' => 'Draft brief']);
    Newsletter::factory()->sent(50)->count(3)->create(['subject' => 'Verzonden brief']);

    $this->actingAs($this->editor)
        ->get(route('admin.newsletters.index', ['status' => 'sent']))
        ->assertOk()
        ->assertDontSee('Draft brief')
        ->assertSee('Verzonden brief');
});

it('zoekt op subject', function () {
    Newsletter::factory()->create(['subject' => 'Zomervakantie Toscane']);
    Newsletter::factory()->create(['subject' => 'Winter in de Alpen']);

    $this->actingAs($this->editor)
        ->get(route('admin.newsletters.index', ['search' => 'Toscane']))
        ->assertOk()
        ->assertSee('Zomervakantie Toscane')
        ->assertDontSee('Winter in de Alpen');
});

it('valt terug op default sort bij ongeldige sort-parameter', function () {
    Newsletter::factory()->count(3)->create();

    $this->actingAs($this->editor)
        ->get(route('admin.newsletters.index', ['sort' => 'malicious_column']))
        ->assertOk();
});

it('pagineert op 20 per pagina', function () {
    Newsletter::factory()->count(25)->create();

    $response = $this->actingAs($this->editor)
        ->get(route('admin.newsletters.index'))
        ->assertOk();

    // Paginatielink naar pagina 2 moet bestaan
    $response->assertSee('page=2', false);
});
