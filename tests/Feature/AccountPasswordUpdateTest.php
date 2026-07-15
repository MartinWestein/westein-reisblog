<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'lid', 'guard_name' => 'web']);
});

it('allows a user to update their password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password-123'),
        'email_verified_at' => now(),
    ]);
    $user->assignRole('lid');

    actingAs($user)
        ->from('/mijn-account')
        ->put('/user/password', [
            'current_password' => 'old-password-123',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])
        ->assertRedirect('/mijn-account')
        ->assertSessionHas('status', 'password-updated');

    expect(Hash::check('new-password-456', $user->fresh()->password))->toBeTrue();
});

it('rejects wrong current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password-123'),
        'email_verified_at' => now(),
    ]);
    $user->assignRole('lid');

    actingAs($user)
        ->from('/mijn-account')
        ->put('/user/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])
        ->assertRedirect('/mijn-account')
        ->assertSessionHasErrorsIn('updatePassword');

    expect(Hash::check('old-password-123', $user->fresh()->password))->toBeTrue();
});

it('rejects mismatched password confirmation', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password-123'),
        'email_verified_at' => now(),
    ]);
    $user->assignRole('lid');

    actingAs($user)
        ->from('/mijn-account')
        ->put('/user/password', [
            'current_password' => 'old-password-123',
            'password' => 'new-password-456',
            'password_confirmation' => 'different-value',
        ])
        ->assertRedirect('/mijn-account')
        ->assertSessionHasErrorsIn('updatePassword');
});

it('renders the password card on the account page', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('lid');

    actingAs($user)
        ->get('/mijn-account')
        ->assertOk()
        ->assertSee('Wachtwoord wijzigen')
        ->assertSee('Huidig wachtwoord')
        ->assertSee('Nieuw wachtwoord')
        ->assertSee('Bevestig nieuw wachtwoord');
});
