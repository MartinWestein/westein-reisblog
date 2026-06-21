<?php

use App\Mail\SubscriberConfirmationMail;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach ([
        'subscribers.manage',
    ] as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web'])
        ->givePermissionTo('subscribers.manage');
    Role::firstOrCreate(['name' => 'auteur', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'lid', 'guard_name' => 'web']);
});

// --------------------------------------------------------------------
// RBAC
// --------------------------------------------------------------------

it('weigert gasten op de index', function () {
    $this->get(route('admin.subscribers.index'))->assertRedirect(route('login'));
});

it('weigert lid op de index', function () {
    $member = User::factory()->create();
    $member->assignRole('lid');

    $this->actingAs($member)->get(route('admin.subscribers.index'))->assertForbidden();
});

it('weigert auteur op de index', function () {
    $author = User::factory()->create();
    $author->assignRole('auteur');

    $this->actingAs($author)->get(route('admin.subscribers.index'))->assertForbidden();
});

it('staat editor toe op de index', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)->get(route('admin.subscribers.index'))->assertOk();
});

it('staat admin toe op de index', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get(route('admin.subscribers.index'))->assertOk();
});

// --------------------------------------------------------------------
// CRUD
// --------------------------------------------------------------------

it('toont de index met counts per status', function () {
    Subscriber::factory()->count(3)->pending()->create();
    Subscriber::factory()->count(2)->confirmed()->create();
    Subscriber::factory()->count(1)->unsubscribed()->create();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->get(route('admin.subscribers.index'))
        ->assertOk()
        ->assertSee('(3)')   // pending count
        ->assertSee('(2)')   // active count
        ->assertSee('(1)');  // unsubscribed count
});

it('filtert op status', function () {
    Subscriber::factory()->count(3)->pending()->create();
    Subscriber::factory()->count(2)->confirmed()->create();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->get(route('admin.subscribers.index', ['status' => 'active']))
        ->assertOk()
        ->assertViewHas('subscribers', fn ($p) => $p->total() === 2);
});

it('zoekt op email', function () {
    Subscriber::factory()->create(['email' => 'jansen@voorbeeld.nl']);
    Subscriber::factory()->create(['email' => 'devries@voorbeeld.nl']);

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->get(route('admin.subscribers.index', ['search' => 'jansen']))
        ->assertOk()
        ->assertViewHas('subscribers', fn ($p) => $p->total() === 1);
});

it('maakt een nieuwe pending abonnee aan en verstuurt confirmation-mail', function () {
    Mail::fake();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $response = $this->actingAs($editor)
        ->post(route('admin.subscribers.store'), [
            'email' => 'nieuw@voorbeeld.nl',
            'name' => 'Anna Jansen',
        ]);

    $response
        ->assertRedirect(route('admin.subscribers.index'))
        ->assertSessionHas('success');

    $subscriber = Subscriber::where('email', 'nieuw@voorbeeld.nl')->first();
    expect($subscriber)->not->toBeNull()
        ->and($subscriber->name)->toBe('Anna Jansen')
        ->and($subscriber->isConfirmed())->toBeFalse()
        ->and($subscriber->confirmation_token)->not->toBeEmpty()
        ->and($subscriber->unsubscribe_token)->not->toBeEmpty();

    Mail::assertQueued(SubscriberConfirmationMail::class, fn ($m) => $m->subscriber->is($subscriber));
});

it('normaliseert email naar lowercase en trimt', function () {
    Mail::fake();
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->post(route('admin.subscribers.store'), [
            'email' => '  Anna@VoorBeeld.NL  ',
            'name' => '  Anna  ',
        ])
        ->assertRedirect();

    expect(Subscriber::first())
        ->email->toBe('anna@voorbeeld.nl')
        ->name->toBe('Anna');
});

it('weigert dubbele email-adressen', function () {
    Subscriber::factory()->create(['email' => 'bestaat@voorbeeld.nl']);

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->post(route('admin.subscribers.store'), ['email' => 'bestaat@voorbeeld.nl'])
        ->assertSessionHasErrors('email');
});

it('bewerkt een bestaande abonnee', function () {
    $subscriber = Subscriber::factory()->confirmed()->create();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->put(route('admin.subscribers.update', $subscriber), [
            'email' => $subscriber->email,
            'name' => 'Nieuwe Naam',
        ])
        ->assertRedirect(route('admin.subscribers.index'))
        ->assertSessionHas('success');

    expect($subscriber->fresh()->name)->toBe('Nieuwe Naam');
});

it('verwijdert een abonnee (hard delete, geen soft delete)', function () {
    $subscriber = Subscriber::factory()->create();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->delete(route('admin.subscribers.destroy', $subscriber))
        ->assertRedirect(route('admin.subscribers.index'));

    expect(Subscriber::find($subscriber->id))->toBeNull();
});

// --------------------------------------------------------------------
// Verb-routes: send-confirmation + bulk
// --------------------------------------------------------------------

it('verstuurt bevestigingsmail naar één pending abonnee', function () {
    Mail::fake();
    $subscriber = Subscriber::factory()->pending()->create();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->post(route('admin.subscribers.send-confirmation', $subscriber))
        ->assertRedirect()
        ->assertSessionHas('success');

    Mail::assertQueued(SubscriberConfirmationMail::class);
});

it('skipt mail-dispatch voor reeds bevestigde abonnee', function () {
    Mail::fake();
    $subscriber = Subscriber::factory()->confirmed()->create();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->post(route('admin.subscribers.send-confirmation', $subscriber))
        ->assertRedirect()
        ->assertSessionHas('warning');

    Mail::assertNothingQueued();
});

it('skipt mail-dispatch voor uitgeschreven abonnee', function () {
    Mail::fake();
    $subscriber = Subscriber::factory()->unsubscribed()->create();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->post(route('admin.subscribers.send-confirmation', $subscriber))
        ->assertRedirect()
        ->assertSessionHas('warning');

    Mail::assertNothingQueued();
});

it('verstuurt bulk-confirmations naar alle pending', function () {
    Mail::fake();
    Subscriber::factory()->count(4)->pending()->create();
    Subscriber::factory()->count(2)->confirmed()->create();
    Subscriber::factory()->count(1)->unsubscribed()->create();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->post(route('admin.subscribers.send-bulk-confirmations'))
        ->assertRedirect()
        ->assertSessionHas('success');

    Mail::assertQueued(SubscriberConfirmationMail::class, 4);
});

// --------------------------------------------------------------------
// Model defaults
// --------------------------------------------------------------------

it('genereert automatisch unsubscribe_token bij creatie', function () {
    $subscriber = Subscriber::create(['email' => 'auto@voorbeeld.nl']);

    expect($subscriber->unsubscribe_token)->not->toBeEmpty()
        ->and(strlen($subscriber->unsubscribe_token))->toBe(64);
});

it('genereert automatisch confirmation_token bij creatie indien onbevestigd', function () {
    $subscriber = Subscriber::create(['email' => 'auto2@voorbeeld.nl']);

    expect($subscriber->confirmation_token)->not->toBeEmpty()
        ->and(strlen($subscriber->confirmation_token))->toBe(64);
});

it('geeft de juiste status terug', function () {
    expect(Subscriber::factory()->pending()->create()->status())->toBe('pending');
    expect(Subscriber::factory()->confirmed()->create()->status())->toBe('active');
    expect(Subscriber::factory()->unsubscribed()->create()->status())->toBe('unsubscribed');
});
