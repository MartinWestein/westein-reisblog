<?php

use App\Listeners\MarkEmailVerifiedAfterPasswordReset;
use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

test('admin ziet het create-form', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertSee(__('Nieuwe gebruiker'))
        ->assertSee(__('Uitnodiging versturen'));
});

test('store maakt user aan met gekozen rollen', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Mail::fake();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Nieuwe Gebruiker',
            'email' => 'nieuwe@voorbeeld.nl',
            'roles' => ['editor', 'lid'],
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    $user = User::where('email', 'nieuwe@voorbeeld.nl')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Nieuwe Gebruiker');
    expect($user->hasRole('editor'))->toBeTrue();
    expect($user->hasRole('lid'))->toBeTrue();
    expect($user->hasRole('auteur'))->toBeFalse();
});

test('store verstuurt uitnodigingsmail', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Mail::fake();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Nieuwe Gebruiker',
            'email' => 'invite@voorbeeld.nl',
            'roles' => ['lid'],
        ]);

    Mail::assertQueued(UserInvitationMail::class, function ($mail) {
        return $mail->hasTo('invite@voorbeeld.nl');
    });
});

test('store zet email_verified_at niet bij aanmaken', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Mail::fake();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Onbevestigd',
            'email' => 'onbevestigd@voorbeeld.nl',
            'roles' => ['lid'],
        ]);

    $user = User::where('email', 'onbevestigd@voorbeeld.nl')->first();
    expect($user->email_verified_at)->toBeNull();
});

test('store valideert vereiste velden', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Mail::fake();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => '',
            'email' => '',
        ])
        ->assertSessionHasErrors(['name', 'email']);

    Mail::assertNothingOutgoing();
});

test('store valideert email-uniekheid', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    User::factory()->create(['email' => 'bestaat@voorbeeld.nl']);

    Mail::fake();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Dubbel',
            'email' => 'bestaat@voorbeeld.nl',
            'roles' => ['lid'],
        ])
        ->assertSessionHasErrors('email');

    Mail::assertNothingOutgoing();
});

test('store valideert rol-whitelist', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Mail::fake();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Tampering Test',
            'email' => 'tamper@voorbeeld.nl',
            'roles' => ['fake-role', 'super-admin'],
        ])
        ->assertSessionHasErrors('roles.0');

    Mail::assertNothingOutgoing();
});

test('listener zet email_verified_at bij PasswordReset op unverified user', function () {
    $user = User::factory()->unverified()->create();
    expect($user->email_verified_at)->toBeNull();

    (new MarkEmailVerifiedAfterPasswordReset)->handle(new PasswordReset($user));

    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull();
});

test('listener is no-op op al geverifieerde user', function () {
    $verifiedAt = now()->subDays(5);
    $user = User::factory()->create(['email_verified_at' => $verifiedAt]);

    (new MarkEmailVerifiedAfterPasswordReset)->handle(new PasswordReset($user));

    $user->refresh();
    expect($user->email_verified_at->timestamp)->toBe($verifiedAt->timestamp);
});
test('edit-form rendert met user-data pre-filled', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $target = User::factory()->create(['name' => 'Doel Gebruiker', 'email' => 'doel@test.local']);
    $target->assignRole('editor');

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $target))
        ->assertOk()
        ->assertSee('Doel Gebruiker')
        ->assertSee('doel@test.local');
});

test('update wijzigt naam, email en rollen zonder email-change', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Mail::fake();

    $target = User::factory()->create(['email' => 'origineel@test.local']);
    $target->assignRole('lid');

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $target), [
            'name' => 'Nieuwe Naam',
            'email' => 'origineel@test.local',
            'roles' => ['editor'],
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    $target->refresh();
    expect($target->name)->toBe('Nieuwe Naam');
    expect($target->email)->toBe('origineel@test.local');
    expect($target->hasRole('editor'))->toBeTrue();
    expect($target->hasRole('lid'))->toBeFalse();

    Mail::assertNothingOutgoing();
});

test('update met email-change reset email_verified_at', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Mail::fake();

    $target = User::factory()->create([
        'email' => 'oud@test.local',
        'email_verified_at' => now()->subDays(30),
    ]);
    $target->assignRole('lid');

    expect($target->email_verified_at)->not->toBeNull();

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => 'nieuw@test.local',
            'roles' => ['lid'],
        ]);

    $target->refresh();
    expect($target->email)->toBe('nieuw@test.local');
    expect($target->email_verified_at)->toBeNull();
});

test('update met email-change triggert invite-mail naar nieuwe adres', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Mail::fake();

    $target = User::factory()->create(['email' => 'oud@test.local']);
    $target->assignRole('lid');

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => 'nieuw@test.local',
            'roles' => ['lid'],
        ]);

    Mail::assertQueued(UserInvitationMail::class, function ($mail) {
        return $mail->hasTo('nieuw@test.local');
    });
});

test('update zonder email-change triggert geen invite-mail', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Mail::fake();

    $target = User::factory()->create(['email' => 'zelfde@test.local']);
    $target->assignRole('lid');

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $target), [
            'name' => 'Andere Naam',
            'email' => 'zelfde@test.local',
            'roles' => ['editor'],
        ]);

    Mail::assertNothingOutgoing();
});

test('update valideert vereiste velden', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $target = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $target), [
            'name' => '',
            'email' => '',
        ])
        ->assertSessionHasErrors(['name', 'email']);
});

test('update valideert email-uniekheid tegen andere users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    User::factory()->create(['email' => 'bestaat@test.local']);
    $target = User::factory()->create(['email' => 'target@test.local']);

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => 'bestaat@test.local',
            'roles' => ['lid'],
        ])
        ->assertSessionHasErrors('email');
});

test('update laat user z\'n eigen email houden', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $target = User::factory()->create(['email' => 'eigen@test.local']);
    $target->assignRole('lid');

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $target), [
            'name' => 'Nieuwe Naam',
            'email' => 'eigen@test.local',
            'roles' => ['lid'],
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasNoErrors();
});

test('update valideert rol-whitelist', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $target = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'roles' => ['fake-role'],
        ])
        ->assertSessionHasErrors('roles.0');
});

test('F4-U2 guard: admin kan eigen admin-rol niet verwijderen', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Zorg dat er nog een andere actieve admin is, zodat F4-U10 niet triggert
    $tweede = User::factory()->create();
    $tweede->assignRole('admin');

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'roles' => ['lid'],
        ])
        ->assertSessionHasErrors('roles');

    $admin->refresh();
    expect($admin->hasRole('admin'))->toBeTrue();
});

test('admin kan wel andere admin z\'n admin-rol verwijderen', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $tweede = User::factory()->create();
    $tweede->assignRole('admin');

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $tweede), [
            'name' => $tweede->name,
            'email' => $tweede->email,
            'roles' => ['editor'],
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasNoErrors();

    $tweede->refresh();
    expect($tweede->hasRole('admin'))->toBeFalse();
    expect($tweede->hasRole('editor'))->toBeTrue();
});

test('F4-U10 guard: laatste actieve admin behoudt admin-rol', function () {
    // Enige admin in het systeem
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'roles' => ['lid'],
        ])
        ->assertSessionHasErrors('roles');

    $admin->refresh();
    expect($admin->hasRole('admin'))->toBeTrue();
});

test('F4-U10 guard: gedeactiveerde admin telt niet mee als actief', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Tweede admin is gedeactiveerd - telt niet als "actieve admin"
    $gedeactiveerd = User::factory()->create(['deactivated_at' => now()]);
    $gedeactiveerd->assignRole('admin');

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'roles' => ['lid'],
        ])
        ->assertSessionHasErrors('roles');

    $admin->refresh();
    expect($admin->hasRole('admin'))->toBeTrue();
});

test('F4-U10 guard: met meerdere actieve admins mag er eentje degraderen', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $tweede = User::factory()->create();
    $tweede->assignRole('admin');

    // Admin degradeert tweede naar lid - werkt want admin blijft over als actieve admin
    $this->actingAs($admin)
        ->patch(route('admin.users.update', $tweede), [
            'name' => $tweede->name,
            'email' => $tweede->email,
            'roles' => ['lid'],
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasNoErrors();

    $tweede->refresh();
    expect($tweede->hasRole('admin'))->toBeFalse();
    expect($tweede->hasRole('lid'))->toBeTrue();
});

test('deactivate happy path met reden', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Zorg dat er nog een actieve admin is (F4-U10 anders triggert)
    $adminBackup = User::factory()->create();
    $adminBackup->assignRole('admin');

    $target = User::factory()->create();
    $target->assignRole('lid');

    $this->actingAs($admin)
        ->post(route('admin.users.deactivate', $target), [
            'reason' => 'Op eigen verzoek',
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    $target->refresh();
    expect($target->deactivated_at)->not->toBeNull();
    expect($target->deactivation_reason)->toBe('Op eigen verzoek');
});

test('deactivate zonder reden werkt', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $target = User::factory()->create();
    $target->assignRole('lid');

    $this->actingAs($admin)
        ->post(route('admin.users.deactivate', $target))
        ->assertRedirect(route('admin.users.index'));

    $target->refresh();
    expect($target->deactivated_at)->not->toBeNull();
    expect($target->deactivation_reason)->toBeNull();
});

test('F4-U2 guard: admin kan zichzelf niet deactiveren', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $adminBackup = User::factory()->create();
    $adminBackup->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.users.deactivate', $admin))
        ->assertSessionHasErrors('reason');

    $admin->refresh();
    expect($admin->deactivated_at)->toBeNull();
});

test('F4-U10 guard: laatste actieve admin kan niet gedeactiveerd worden', function () {
    // Enige admin in het systeem
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Andere admin die gedeactiveerd is (telt niet als actief)
    $gedeactiveerdeAdmin = User::factory()->create(['deactivated_at' => now()]);
    $gedeactiveerdeAdmin->assignRole('admin');

    // We hebben een tweede admin nodig om de eerste te kunnen bewerken zonder self-lock
    // Dus laten we een echte second-admin maken die de deactivate doet
    $tweedeAdmin = User::factory()->create();
    $tweedeAdmin->assignRole('admin');

    // Nu zijn er twee actieve admins: $admin en $tweedeAdmin
    // We probeer $admin te deactiveren via $tweedeAdmin
    // Dat zou moeten werken want er blijft nog een admin over ($tweedeAdmin)
    // Dus voor de last-admin-test moeten we juist de laatste actieve admin proberen te deactiveren

    // Herstel scenario: één actieve admin ($tweedeAdmin), één gedeactiveerde ($admin nu al gedeactiveerd)
    $admin->deactivated_at = now();
    $admin->save();

    // Nu is $tweedeAdmin de enige actieve admin
    // Een andere admin proberen $tweedeAdmin te deactiveren - maar er is geen andere admin!
    // Oplossing: gebruik $tweedeAdmin zelf, maar F4-U2 zou dan triggeren

    // Cleanest: laat $admin de acting-user zijn, $admin is de enige actieve admin,
    // en probeer $admin te deactiveren. Dat is self-lock (F4-U2) + last-admin (F4-U10)
    // beide - matcht F4-U19 (beide meldingen).

    // Voor pure F4-U10 test zonder F4-U2: maak twee admins waarvan één acting, ander is target
    // en target is de enige actieve admin - dat kan alleen als acting geen admin is
    // maar dan komt 'ie niet door de policy heen.

    // Conclusie: F4-U10 alleen is niet zinvol testbaar zonder F4-U2 in familieblog-context.
    // We testen dat het scenario "laatste actieve admin" blokkeert, ongeacht welke guard 't oppikt.

    // Reset test-fixture voor deze specifieke check:
    User::query()->delete();

    $enigeAdmin = User::factory()->create();
    $enigeAdmin->assignRole('admin');

    $this->actingAs($enigeAdmin)
        ->post(route('admin.users.deactivate', $enigeAdmin))
        ->assertSessionHasErrors('reason');

    $enigeAdmin->refresh();
    expect($enigeAdmin->deactivated_at)->toBeNull();
});

test('reactivate reset deactivated_at en deactivation_reason', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $target = User::factory()->create([
        'deactivated_at' => now()->subDays(5),
        'deactivation_reason' => 'Was tijdelijk uitgeschakeld',
    ]);
    $target->assignRole('lid');

    $this->actingAs($admin)
        ->post(route('admin.users.reactivate', $target))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    $target->refresh();
    expect($target->deactivated_at)->toBeNull();
    expect($target->deactivation_reason)->toBeNull();
});

test('Fortify blokkeert login van gedeactiveerde user', function () {
    $user = User::factory()->create([
        'password' => bcrypt('geheim-wachtwoord'),
        'deactivated_at' => now(),
    ]);
    $user->assignRole('lid');

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'geheim-wachtwoord',
    ]);

    $this->assertGuest();
});

test('Fortify staat login toe voor actieve user', function () {
    $user = User::factory()->create([
        'password' => bcrypt('geheim-wachtwoord'),
        'email_verified_at' => now(),
    ]);
    $user->assignRole('lid');

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'geheim-wachtwoord',
    ]);

    $this->assertAuthenticatedAs($user);
});
