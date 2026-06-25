<?php

use App\Models\Newsletter;
use App\Models\Subscriber;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'newsletters.manage', 'guard_name' => 'web']);

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web'])
        ->givePermissionTo('newsletters.manage');
    Role::firstOrCreate(['name' => 'lid', 'guard_name' => 'web']);
});

it('toont de verzend-knop op de edit-pagina van een draft voor editor', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    Subscriber::factory()->count(3)->confirmed()->create();
    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    $this->actingAs($editor)
        ->get(route('admin.newsletters.edit', $newsletter))
        ->assertOk()
        ->assertSee('Verzend nieuwsbrief')
        ->assertSee('newsletterDispatchModal');
});

it('toont GEEN verzend-knop op de create-pagina', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    Subscriber::factory()->count(3)->confirmed()->create();

    $this->actingAs($editor)
        ->get(route('admin.newsletters.create'))
        ->assertOk()
        ->assertDontSee('Verzend nieuwsbrief')
        ->assertDontSee('newsletterDispatchModal');
});

it('toont GEEN verzend-knop bij een nieuwsbrief die wordt verzonden', function () {
    // Editor mag sending-newsletters niet eens editen, maar als 'ie er
    // toch komt (admin via Gate::before) mag de modal er niet zijn.
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Subscriber::factory()->count(3)->confirmed()->create();
    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_SENDING]);

    $this->actingAs($admin)
        ->get(route('admin.newsletters.edit', $newsletter))
        ->assertOk()
        ->assertDontSee('Verzend nieuwsbrief')
        ->assertDontSee('newsletterDispatchModal');
});

it('toont GEEN verzend-knop bij een reeds verzonden nieuwsbrief', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Subscriber::factory()->count(3)->confirmed()->create();
    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_SENT]);

    $this->actingAs($admin)
        ->get(route('admin.newsletters.edit', $newsletter))
        ->assertOk()
        ->assertDontSee('Verzend nieuwsbrief')
        ->assertDontSee('newsletterDispatchModal');
});

it('toont de verzend-knop disabled wanneer er geen actieve abonnees zijn', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    Subscriber::factory()->count(2)->pending()->create(); // niet-actief
    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    $response = $this->actingAs($editor)
        ->get(route('admin.newsletters.edit', $newsletter))
        ->assertOk()
        ->assertSee('Verzend nieuwsbrief');

    // Knop staat er, maar met disabled-attribuut
    expect($response->getContent())->toMatch('/<button[^>]*disabled[^>]*>[\s\S]*Verzend nieuwsbrief/');
});

it('modal toont onderwerp, sjabloon en aantal ontvangers', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    Subscriber::factory()->count(4)->confirmed()->create();
    $newsletter = Newsletter::factory()->create([
        'status' => Newsletter::STATUS_DRAFT,
        'subject' => 'Zomervakantie 2026 update',
        'template' => Newsletter::TEMPLATE_ANNOUNCEMENT,
    ]);

    $this->actingAs($editor)
        ->get(route('admin.newsletters.edit', $newsletter))
        ->assertOk()
        ->assertSee('Zomervakantie 2026 update')
        ->assertSee('Aankondiging') // template-label
        ->assertSee('4 actieve abonnees');
});
