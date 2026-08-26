<?php

use App\Models\Destination;

use function Pest\Laravel\get;

it('rendert WebSite + Organization JSON-LD site-breed', function () {
    get('/')
        ->assertOk()
        ->assertSee('application/ld+json', false)
        ->assertSee('"@type":"WebSite"', false)
        ->assertSee('"@type":"Organization"', false)
        ->assertSee('images/logo.png', false);
});

it('rendert een BreadcrumbList op een detail-pagina met kruimelpad', function () {
    Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);

    get('/bestemmingen/italie')
        ->assertOk()
        ->assertSee('"@type":"BreadcrumbList"', false)
        ->assertSee('"@type":"ListItem"', false);
});
