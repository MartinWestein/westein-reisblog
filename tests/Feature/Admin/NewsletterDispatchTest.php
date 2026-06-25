<?php

use App\Jobs\SendNewsletterJob;
use App\Models\Newsletter;
use App\Models\NewsletterSend;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
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

it('weigert gasten op de dispatch-route', function () {
    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    $this->post(route('admin.newsletters.dispatch', $newsletter))
        ->assertRedirect(route('login'));
});

it('weigert lid op de dispatch-route', function () {
    $member = User::factory()->create();
    $member->assignRole('lid');

    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    $this->actingAs($member)
        ->post(route('admin.newsletters.dispatch', $newsletter))
        ->assertForbidden();
});

it('weigert auteur op de dispatch-route', function () {
    $author = User::factory()->create();
    $author->assignRole('auteur');

    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    $this->actingAs($author)
        ->post(route('admin.newsletters.dispatch', $newsletter))
        ->assertForbidden();
});

// -----------------------------------------------------------------------------
// Status-guards (Policy)
// -----------------------------------------------------------------------------

it('weigert dispatch op een nieuwsbrief die wordt verzonden', function () {
    Bus::fake();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    Subscriber::factory()->count(2)->confirmed()->create();
    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_SENDING]);

    $this->actingAs($editor)
        ->post(route('admin.newsletters.dispatch', $newsletter))
        ->assertForbidden();

    Bus::assertNothingBatched();
});

it('weigert dispatch op een reeds verzonden nieuwsbrief', function () {
    Bus::fake();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    Subscriber::factory()->count(2)->confirmed()->create();
    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_SENT]);

    $this->actingAs($editor)
        ->post(route('admin.newsletters.dispatch', $newsletter))
        ->assertForbidden();

    Bus::assertNothingBatched();
});

// -----------------------------------------------------------------------------
// Request-validator (zero subscribers)
// -----------------------------------------------------------------------------

it('weigert dispatch wanneer er geen actieve abonnees zijn', function () {
    Bus::fake();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    Subscriber::factory()->count(3)->pending()->create(); // niet actief
    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    $this->actingAs($editor)
        ->post(route('admin.newsletters.dispatch', $newsletter))
        ->assertRedirect()
        ->assertSessionHasErrors('recipients');

    Bus::assertNothingBatched();
    expect(NewsletterSend::where('newsletter_id', $newsletter->id)->count())->toBe(0);
    expect($newsletter->fresh()->status)->toBe(Newsletter::STATUS_DRAFT);
});

// -----------------------------------------------------------------------------
// Happy path
// -----------------------------------------------------------------------------

it('dispatcht een draft-nieuwsbrief en redirect met flash', function () {
    Bus::fake();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    Subscriber::factory()->count(4)->confirmed()->create();
    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    $this->actingAs($editor)
        ->post(route('admin.newsletters.dispatch', $newsletter))
        ->assertRedirect(route('admin.newsletters.index'))
        ->assertSessionHas('success');

    expect($newsletter->fresh()->status)->toBe(Newsletter::STATUS_SENDING)
        ->and($newsletter->fresh()->recipients_count)->toBe(4)
        ->and(NewsletterSend::where('newsletter_id', $newsletter->id)->count())->toBe(4);

    Bus::assertBatched(
        fn ($batch) => $batch->jobs->count() === 4
            && $batch->jobs->every(fn ($job) => $job instanceof SendNewsletterJob)
    );
});

it('flash-bericht toont juiste pluralisering bij Ã©Ã©n abonnee', function () {
    Bus::fake();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    Subscriber::factory()->confirmed()->create();
    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    $response = $this->actingAs($editor)
        ->post(route('admin.newsletters.dispatch', $newsletter))
        ->assertRedirect();

    expect(session('success'))->toContain('1')->toContain('abonnee');
});

it('skipt non-actieve subscribers bij dispatch', function () {
    Bus::fake();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    Subscriber::factory()->count(2)->confirmed()->create();
    Subscriber::factory()->count(3)->pending()->create();
    Subscriber::factory()->count(1)->unsubscribed()->create();

    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    $this->actingAs($editor)
        ->post(route('admin.newsletters.dispatch', $newsletter))
        ->assertRedirect();

    expect($newsletter->fresh()->recipients_count)->toBe(2);
    expect(NewsletterSend::where('newsletter_id', $newsletter->id)->count())->toBe(2);

    Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 2);
});
