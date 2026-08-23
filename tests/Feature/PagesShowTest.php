<?php

use App\Models\Page;

use function Pest\Laravel\get;

it('toont een gepubliceerde statische pagina via de catch-all', function () {
    Page::factory()->create([
        'title' => 'Privacyverklaring',
        'slug' => 'privacy',
        'body' => '<p>Zo gaan we met je gegevens om.</p>',
        'published_at' => now()->subDay(),
    ]);

    get('/privacy')
        ->assertOk()
        ->assertSee('Privacyverklaring')
        ->assertSee('Zo gaan we met je gegevens om', false);
});

it('geeft 404 voor een niet-gepubliceerde pagina', function () {
    Page::factory()->create(['title' => 'Concept', 'slug' => 'concept', 'published_at' => null]);

    get('/concept')->assertNotFound();
});

it('geeft 404 voor een pagina met een toekomstige publicatiedatum', function () {
    Page::factory()->create(['title' => 'Binnenkort', 'slug' => 'binnenkort', 'published_at' => now()->addWeek()]);

    get('/binnenkort')->assertNotFound();
});

it('geeft 404 voor een onbekende slug', function () {
    get('/bestaat-echt-niet')->assertNotFound();
});

it('laat een named route voorgaan op de catch-all', function () {
    // Een pagina met slug 'verhalen' mag de /verhalen-index niet kapen.
    Page::factory()->create([
        'title' => 'Verhalen',
        'slug' => 'verhalen',
        'body' => '<p>Deze mag niet op /verhalen verschijnen.</p>',
        'published_at' => now()->subDay(),
    ]);

    get('/verhalen')
        ->assertOk()
        ->assertDontSee('Deze mag niet op /verhalen verschijnen', false);
});

it('kaapt de admin-route niet (pagina-fallback vs echte route)', function () {
    // Als gast redirect /admin naar login; een gekaapte route zou 404 geven.
    get(route('admin.home'))->assertRedirect(route('login'));
});
