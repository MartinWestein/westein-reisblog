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
it('toont het create-formulier voor editor', function () {
    $this->actingAs($this->editor)
        ->get(route('admin.newsletters.create'))
        ->assertOk()
        ->assertSee(__('Onderwerp'))
        ->assertSee(__('Sjabloon'))
        ->assertSee(__('Bericht'));
});

it('weigert create voor lid', function () {
    $lid = User::factory()->create();
    $lid->assignRole('lid');

    $this->actingAs($lid)
        ->get(route('admin.newsletters.create'))
        ->assertForbidden();
});

it('slaat een nieuwe concept-nieuwsbrief op en redirect naar edit', function () {
    $payload = [
        'subject' => 'Zomerse groeten uit Toscane',
        'template' => Newsletter::TEMPLATE_DIGEST,
        'body' => '<p>Beste lezer, lees onze laatste avonturen!</p>',
    ];

    $response = $this->actingAs($this->editor)
        ->post(route('admin.newsletters.store'), $payload);

    $newsletter = Newsletter::query()->latest('id')->first();

    $response->assertRedirect(route('admin.newsletters.edit', $newsletter));

    expect($newsletter->subject)->toBe('Zomerse groeten uit Toscane')
        ->and($newsletter->template)->toBe(Newsletter::TEMPLATE_DIGEST)
        ->and($newsletter->status)->toBe(Newsletter::STATUS_DRAFT)
        ->and($newsletter->user_id)->toBe($this->editor->id)
        ->and($newsletter->body)->toContain('Beste lezer');
});

it('valideert verplichte velden bij store', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.newsletters.store'), [])
        ->assertSessionHasErrors(['subject', 'template', 'body']);
});

it('weigert een ongeldig template bij store', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.newsletters.store'), [
            'subject' => 'Test',
            'template' => 'spam-template',
            'body' => '<p>Body</p>',
        ])
        ->assertSessionHasErrors('template');
});

it('saneert HTML in body via simple Purifier-profiel', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.newsletters.store'), [
            'subject' => 'Sanitize test',
            'template' => Newsletter::TEMPLATE_PLAIN,
            'body' => '<p>Veilig</p><script>alert("xss")</script>',
        ]);

    $newsletter = Newsletter::query()->latest('id')->first();

    expect($newsletter->body)->toContain('Veilig')
        ->and($newsletter->body)->not->toContain('<script>');
});

it('toont het edit-formulier voor een draft', function () {
    $newsletter = Newsletter::factory()->for($this->editor, 'author')->create([
        'subject' => 'Bestaande draft',
    ]);

    $this->actingAs($this->editor)
        ->get(route('admin.newsletters.edit', $newsletter))
        ->assertOk()
        ->assertSee('Bestaande draft');
});

it('weigert edit voor een sent nieuwsbrief (editor wordt geblokkeerd door status-guard)', function () {
    $newsletter = Newsletter::factory()->for($this->editor, 'author')->sent(50)->create();

    $this->actingAs($this->editor)
        ->get(route('admin.newsletters.edit', $newsletter))
        ->assertForbidden();
});

it('werkt een draft bij via update', function () {
    $newsletter = Newsletter::factory()->for($this->editor, 'author')->create([
        'subject' => 'Oud onderwerp',
    ]);

    $response = $this->actingAs($this->editor)
        ->put(route('admin.newsletters.update', $newsletter), [
            'subject' => 'Nieuw onderwerp',
            'template' => Newsletter::TEMPLATE_ANNOUNCEMENT,
            'body' => '<p>Nieuwe tekst</p>',
        ]);

    $response->assertRedirect(route('admin.newsletters.index'));

    expect($newsletter->fresh()->subject)->toBe('Nieuw onderwerp')
        ->and($newsletter->fresh()->template)->toBe(Newsletter::TEMPLATE_ANNOUNCEMENT);
});

it('blokkeert update op een sent nieuwsbrief (editor)', function () {
    $newsletter = Newsletter::factory()->for($this->editor, 'author')->sent(50)->create();

    $this->actingAs($this->editor)
        ->put(route('admin.newsletters.update', $newsletter), [
            'subject' => 'Stiekem wijzigen',
            'template' => Newsletter::TEMPLATE_PLAIN,
            'body' => '<p>x</p>',
        ])
        ->assertForbidden();
});

it('verwijdert een draft hard', function () {
    $newsletter = Newsletter::factory()->for($this->editor, 'author')->create();

    $this->actingAs($this->editor)
        ->delete(route('admin.newsletters.destroy', $newsletter))
        ->assertRedirect(route('admin.newsletters.index'));

    expect(Newsletter::query()->find($newsletter->id))->toBeNull();
});

it('blokkeert destroy op sent nieuwsbrief (editor)', function () {
    $newsletter = Newsletter::factory()->for($this->editor, 'author')->sent(50)->create();

    $this->actingAs($this->editor)
        ->delete(route('admin.newsletters.destroy', $newsletter))
        ->assertForbidden();

    expect(Newsletter::query()->find($newsletter->id))->not->toBeNull();
});
