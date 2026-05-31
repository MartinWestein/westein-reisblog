<?php

use App\Models\Page;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Rollen + permissie die de policy nodig heeft
    Permission::firstOrCreate(['name' => 'pages.manage', 'guard_name' => 'web']);

    foreach (['admin', 'editor', 'auteur', 'lid'] as $roleName) {
        Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    }

    Role::findByName('editor')->givePermissionTo('pages.manage');

    // Gate::before super-admin shortcut
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');

    $this->author = User::factory()->create();
    $this->author->assignRole('auteur');

    $this->member = User::factory()->create();
    $this->member->assignRole('lid');
});

/*
|--------------------------------------------------------------------------
| Toegang — RBAC-matrix
|--------------------------------------------------------------------------
*/

it('toont de index voor een admin', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.pages.index'))
        ->assertOk();
});

it('staat editors toe de index te zien', function () {
    $this->actingAs($this->editor)
        ->get(route('admin.pages.index'))
        ->assertOk();
});

it('weigert auteurs op de index', function () {
    $this->actingAs($this->author)
        ->get(route('admin.pages.index'))
        ->assertForbidden();
});

it('weigert leden op de index', function () {
    $this->actingAs($this->member)
        ->get(route('admin.pages.index'))
        ->assertForbidden();
});

it('stuurt gasten naar login', function () {
    $this->get(route('admin.pages.index'))
        ->assertRedirect(route('login'));
});

/*
|--------------------------------------------------------------------------
| CRUD — basis
|--------------------------------------------------------------------------
*/

it('toont bestaande pagina\'s op de index', function () {
    Page::factory()->create(['title' => 'Mijn testpagina']);

    $this->actingAs($this->admin)
        ->get(route('admin.pages.index'))
        ->assertOk()
        ->assertSee('Mijn testpagina');
});

it('maakt een pagina aan met defaults', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.pages.store'), [
            'title' => 'Over ons',
            'body' => '<p>Welkom op onze familieblog.</p>',
            'order' => 0,
        ])
        ->assertRedirect(route('admin.pages.index'));

    $page = Page::where('title', 'Over ons')->first();

    expect($page)->not->toBeNull()
        ->and($page->slug)->toBe('over-ons')
        ->and($page->published_at)->toBeNull()    // toggle uit → concept
        ->and($page->order)->toBe(0);
});

it('werkt een pagina bij met behoud van de slug', function () {
    $page = Page::factory()->create([
        'title' => 'Origineel',
        'slug' => 'origineel-slug',
        'body' => '<p>Oude tekst.</p>',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.pages.update', $page), [
            'title' => 'Totaal Andere Titel',
            'body' => '<p>Nieuwe tekst.</p>',
            'order' => 0,
        ])
        ->assertRedirect(route('admin.pages.index'));

    expect($page->fresh())
        ->title->toBe('Totaal Andere Titel')
        ->slug->toBe('origineel-slug')              // slug blijft locked
        ->body->toContain('Nieuwe tekst');
});

it('negeert een meegestuurde slug bij update (tamper-bescherming)', function () {
    $page = Page::factory()->create(['slug' => 'beschermd']);

    $this->actingAs($this->admin)
        ->put(route('admin.pages.update', $page), [
            'title' => 'Nieuwe titel',
            'slug' => 'gekaapt',                    // mag genegeerd worden
            'body' => '<p>Tekst.</p>',
            'order' => 0,
        ]);

    expect($page->fresh()->slug)->toBe('beschermd');
});

it('verwijdert een pagina via soft delete', function () {
    $page = Page::factory()->create(['title' => 'Weg ermee']);

    $this->actingAs($this->admin)
        ->delete(route('admin.pages.destroy', $page))
        ->assertRedirect(route('admin.pages.index'));

    // Soft delete: niet in normale query, wel in withTrashed
    expect(Page::find($page->id))->toBeNull()
        ->and(Page::withTrashed()->find($page->id))->not->toBeNull()
        ->and(Page::withTrashed()->find($page->id)->deleted_at)->not->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Publicatie-status
|--------------------------------------------------------------------------
*/

it('blijft concept wanneer de toggle uit staat', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.pages.store'), [
            'title' => 'Concept pagina',
            'body' => '<p>Tekst.</p>',
            'order' => 0,
            // is_published niet meegestuurd = uit
        ]);

    expect(Page::where('title', 'Concept pagina')->first()->published_at)
        ->toBeNull();
});

it('publiceert direct wanneer de toggle aan staat zonder datum', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.pages.store'), [
            'title' => 'Direct gepubliceerd',
            'body' => '<p>Tekst.</p>',
            'is_published' => '1',
            'order' => 0,
        ]);

    $page = Page::where('title', 'Direct gepubliceerd')->first();

    expect($page->published_at)->not->toBeNull()
        ->and($page->published_at->diffInSeconds(now()))->toBeLessThan(5);
});

it('plant een pagina in voor een toekomstige datum', function () {
    $future = now()->addDays(7);

    $this->actingAs($this->admin)
        ->post(route('admin.pages.store'), [
            'title' => 'Geplande pagina',
            'body' => '<p>Tekst.</p>',
            'is_published' => '1',
            'published_at' => $future->format('Y-m-d\TH:i'),
            'order' => 0,
        ]);

    $page = Page::where('title', 'Geplande pagina')->first();

    // published() scope toont 'm nog niet (datum > nu)
    expect($page->published_at->isFuture())->toBeTrue()
        ->and(Page::published()->find($page->id))->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Slug-gedrag
|--------------------------------------------------------------------------
*/

it('weigert een gereserveerde slug', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.pages.store'), [
            'title' => 'Mijn admin pagina',
            'slug' => 'admin',                      // gereserveerd!
            'body' => '<p>Tekst.</p>',
            'order' => 0,
        ])
        ->assertSessionHasErrors('slug');

    expect(Page::where('title', 'Mijn admin pagina')->exists())->toBeFalse();
});

it('weigert ook andere top-level gereserveerde slugs', function () {
    foreach (['bestemmingen', 'reistips', 'login', 'dashboard'] as $reserved) {
        $this->actingAs($this->admin)
            ->post(route('admin.pages.store'), [
                'title' => "Test {$reserved}",
                'slug' => $reserved,
                'body' => '<p>Tekst.</p>',
                'order' => 0,
            ])
            ->assertSessionHasErrors('slug');
    }
});

/*
|--------------------------------------------------------------------------
| Sanitization — HTMLPurifier 'simple'-profiel
|--------------------------------------------------------------------------
*/

it('verwijdert script-tags uit de body bij store', function () {
    $payload = '<p>Veilig.</p><script>alert("XSS")</script><p>Ook veilig.</p>';

    $this->actingAs($this->admin)
        ->post(route('admin.pages.store'), [
            'title' => 'XSS test',
            'body' => $payload,
            'order' => 0,
        ]);

    $body = Page::where('title', 'XSS test')->first()->body;

    expect($body)
        ->not->toContain('<script')
        ->not->toContain('alert(')
        ->toContain('Veilig.')
        ->toContain('Ook veilig.');
});

it('verwijdert script-tags uit de body bij update', function () {
    $page = Page::factory()->create(['body' => '<p>Origineel.</p>']);

    $this->actingAs($this->admin)
        ->put(route('admin.pages.update', $page), [
            'title' => $page->title,
            'body' => '<p>OK.</p><script>alert(1)</script>',
            'order' => 0,
        ]);

    expect($page->fresh()->body)
        ->not->toContain('<script')
        ->toContain('OK.');
});

it('behoudt legitieme tags en voegt rel/target toe aan links', function () {
    $payload = '<p>Tekst met <strong>nadruk</strong> en een '
        .'<a href="https://example.com">link</a>.</p>';

    $this->actingAs($this->admin)
        ->post(route('admin.pages.store'), [
            'title' => 'Legitiem',
            'body' => $payload,
            'order' => 0,
        ]);

    $body = Page::where('title', 'Legitiem')->first()->body;

    expect($body)
        ->toContain('<strong>nadruk</strong>')
        ->toContain('href="https://example.com"')
        ->toContain('target="_blank"')              // HTML.TargetBlank
        ->toContain('rel=');                        // rel="nofollow noopener"
});
