<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'lid', 'guard_name' => 'web']);
});

it('shows the "disabled" state when 2FA is not enabled', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('lid');

    actingAs($user)
        ->get('/mijn-account')
        ->assertOk()
        ->assertSee('Tweefactor-authenticatie')
        ->assertSee('Tweefactor-authenticatie inschakelen')
        ->assertDontSee('Tweefactor-authenticatie is actief.');
});

it('shows the "setup" state when 2FA is enabled but not confirmed', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code-1', 'code-2'])),
        'two_factor_confirmed_at' => null,
    ]);
    $user->assignRole('lid');

    actingAs($user)
        ->get('/mijn-account')
        ->assertOk()
        ->assertSee('Stap 1:', false)
        ->assertSee('Stap 2:', false)
        ->assertSee('6-cijferige code')
        ->assertSee('Setup annuleren');
});

it('shows the "enabled" state when 2FA is confirmed', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code-1', 'code-2', 'code-3'])),
        'two_factor_confirmed_at' => now(),
    ]);
    $user->assignRole('lid');

    actingAs($user)
        ->get('/mijn-account')
        ->assertOk()
        ->assertSee('Tweefactor-authenticatie is actief.')
        ->assertSee('Toon herstelcodes')
        ->assertSee('Herstelcodes opnieuw genereren')
        ->assertSee('Tweefactor-authenticatie uitschakelen');
});

it('redirects legacy /profiel/2fa to /mijn-account#2fa', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('lid');

    actingAs($user)
        ->get('/profiel/2fa')
        ->assertRedirect('/mijn-account#2fa')
        ->assertStatus(301);
});

it('redirects legacy /profiel/2fa for guests to login', function () {
    // Auth-middleware kicks in before the redirect fires.
    $this->get('/profiel/2fa')->assertRedirect(route('login'));
});
