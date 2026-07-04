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
