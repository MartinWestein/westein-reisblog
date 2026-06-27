<?php

use App\Models\Newsletter;
use App\Models\NewsletterSend;
use App\Models\Subscriber;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'newsletters.manage', 'guard_name' => 'web']);

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web'])
        ->givePermissionTo('newsletters.manage');
    Role::firstOrCreate(['name' => 'auteur', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'lid', 'guard_name' => 'web']);
});

// -----------------------------------------------------------------------------
// RBAC
// -----------------------------------------------------------------------------

it('weigert gasten op de show-pagina', function () {
    $newsletter = Newsletter::factory()->create();

    $this->get(route('admin.newsletters.show', $newsletter))
        ->assertRedirect(route('login'));
});

it('weigert lid op de show-pagina', function () {
    $member = User::factory()->create();
    $member->assignRole('lid');

    $newsletter = Newsletter::factory()->create();

    $this->actingAs($member)
        ->get(route('admin.newsletters.show', $newsletter))
        ->assertForbidden();
});

it('weigert auteur op de show-pagina', function () {
    $author = User::factory()->create();
    $author->assignRole('auteur');

    $newsletter = Newsletter::factory()->create();

    $this->actingAs($author)
        ->get(route('admin.newsletters.show', $newsletter))
        ->assertForbidden();
});

it('staat editor toe op de show-pagina', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $newsletter = Newsletter::factory()->create();

    $this->actingAs($editor)
        ->get(route('admin.newsletters.show', $newsletter))
        ->assertOk();
});

// -----------------------------------------------------------------------------
// Draft-state
// -----------------------------------------------------------------------------

it('toont info-alert bij draft-newsletter zonder sends', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    $this->actingAs($editor)
        ->get(route('admin.newsletters.show', $newsletter))
        ->assertOk()
        ->assertSee('nog niet verzonden')
        ->assertDontSee('Totaal')
        ->assertDontSee('Bezorgd');
});

// -----------------------------------------------------------------------------
// KPI-aggregatie
// -----------------------------------------------------------------------------

it('berekent KPI correct: 3 bezorgd + 2 mislukt + 1 in wachtrij', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $newsletter = Newsletter::factory()->create([
        'status' => Newsletter::STATUS_SENT,
        'recipients_count' => 6,
    ]);

    // 3 bezorgd
    NewsletterSend::factory()->count(3)->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => fn () => Subscriber::factory()->confirmed()->create()->id,
        'sent_at' => now(),
    ]);

    // 2 mislukt
    NewsletterSend::factory()->failed()->count(2)->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => fn () => Subscriber::factory()->confirmed()->create()->id,
    ]);

    // 1 in wachtrij
    NewsletterSend::factory()->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => Subscriber::factory()->confirmed()->create()->id,
        'sent_at' => null,
        'failed_at' => null,
    ]);

    $response = $this->actingAs($editor)
        ->get(route('admin.newsletters.show', $newsletter))
        ->assertOk();

    $stats = $response->viewData('stats');
    expect($stats->total)->toBe(6)
        ->and($stats->delivered)->toBe(3)
        ->and($stats->failed)->toBe(2)
        ->and($stats->pending)->toBe(1);
});

it('toont stats als nullen bij sending zonder sends', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_SENDING]);

    $response = $this->actingAs($editor)
        ->get(route('admin.newsletters.show', $newsletter))
        ->assertOk();

    $stats = $response->viewData('stats');
    expect($stats->total)->toBe(0)
        ->and($stats->delivered)->toBe(0)
        ->and($stats->failed)->toBe(0)
        ->and($stats->pending)->toBe(0);
});

// -----------------------------------------------------------------------------
// Filter + sort + paginatie
// -----------------------------------------------------------------------------

it('filtert sends op status=delivered', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_SENT]);

    NewsletterSend::factory()->count(2)->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => fn () => Subscriber::factory()->confirmed()->create()->id,
        'sent_at' => now(),
    ]);
    NewsletterSend::factory()->failed()->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => Subscriber::factory()->confirmed()->create()->id,
    ]);

    $response = $this->actingAs($editor)
        ->get(route('admin.newsletters.show', [$newsletter, 'status' => 'delivered']))
        ->assertOk();

    expect($response->viewData('sends')->total())->toBe(2)
        ->and($response->viewData('statusFilter'))->toBe('delivered');
});

it('filtert sends op status=failed', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_SENT]);

    NewsletterSend::factory()->failed()->count(3)->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => fn () => Subscriber::factory()->confirmed()->create()->id,
    ]);

    NewsletterSend::factory()->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => Subscriber::factory()->confirmed()->create()->id,
        'sent_at' => now(),
    ]);

    $response = $this->actingAs($editor)
        ->get(route('admin.newsletters.show', [$newsletter, 'status' => 'failed']))
        ->assertOk();

    expect($response->viewData('sends')->total())->toBe(3);
});

it('valt terug op all bij onbekende status-filter', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_SENT]);

    NewsletterSend::factory()->count(2)->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => fn () => Subscriber::factory()->confirmed()->create()->id,
        'sent_at' => now(),
    ]);

    $response = $this->actingAs($editor)
        ->get(route('admin.newsletters.show', [$newsletter, 'status' => 'invalid-value']))
        ->assertOk();

    expect($response->viewData('statusFilter'))->toBe('all')
        ->and($response->viewData('sends')->total())->toBe(2);
});

it('valt terug op default sort bij ongeldige sort-parameter', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_SENT]);

    NewsletterSend::factory()->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => Subscriber::factory()->confirmed()->create()->id,
        'sent_at' => now(),
    ]);

    $response = $this->actingAs($editor)
        ->get(route('admin.newsletters.show', [$newsletter, 'sort' => 'password']))
        ->assertOk();

    expect($response->viewData('sort'))->toBe('created_at');
});

it('pagineert sends op 25 per pagina', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_SENT]);

    for ($i = 0; $i < 30; $i++) {
        NewsletterSend::factory()->create([
            'newsletter_id' => $newsletter->id,
            'subscriber_id' => Subscriber::factory()->confirmed()->create()->id,
            'sent_at' => now(),
        ]);
    }

    $response = $this->actingAs($editor)
        ->get(route('admin.newsletters.show', $newsletter))
        ->assertOk();

    expect($response->viewData('sends')->perPage())->toBe(25)
        ->and($response->viewData('sends')->total())->toBe(30)
        ->and($response->viewData('sends')->count())->toBe(25);
});

// -----------------------------------------------------------------------------
// Index-link
// -----------------------------------------------------------------------------

it('index-pagina linkt naar de show-pagina', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $newsletter = Newsletter::factory()->create(['subject' => 'Test onderwerp xyz']);

    $this->actingAs($editor)
        ->get(route('admin.newsletters.index'))
        ->assertOk()
        ->assertSee(route('admin.newsletters.show', $newsletter), false);
});
