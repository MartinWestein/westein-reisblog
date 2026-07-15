<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'lid', 'guard_name' => 'web']);
});

it('redirects guests to login', function () {
    get('/mijn-account')->assertRedirect(route('login'));
});

it('shows the account page for authenticated users', function () {
    $user = User::factory()->create(['name' => 'Jan Westein', 'email_verified_at' => now()]);
    $user->assignRole('lid');

    actingAs($user)
        ->get('/mijn-account')
        ->assertOk()
        ->assertSee('Mijn account')
        ->assertSee('Persoonlijke gegevens')
        ->assertSee('Jan Westein')
        ->assertSee($user->email);
});

it('allows updating the name', function () {
    $user = User::factory()->create(['name' => 'Jan Westein', 'email_verified_at' => now()]);
    $user->assignRole('lid');

    actingAs($user)
        ->put(route('account.update-profile'), ['name' => 'Jan Willem Westein'])
        ->assertRedirect(route('account.show'))
        ->assertSessionHas('success');

    expect($user->fresh()->name)->toBe('Jan Willem Westein');
});

it('rejects an empty name', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('lid');

    actingAs($user)
        ->put(route('account.update-profile'), ['name' => ''])
        ->assertSessionHasErrors('name');
});

it('does not accept email updates via the profile-update route', function () {
    $user = User::factory()->create(['email' => 'original@example.com', 'email_verified_at' => now()]);
    $user->assignRole('lid');

    actingAs($user)
        ->put(route('account.update-profile'), [
            'name' => 'New Name',
            'email' => 'attacker@example.com',
        ])
        ->assertRedirect(route('account.show'));

    expect($user->fresh()->email)->toBe('original@example.com');
});
