<?php

use App\Models\FamilyMember;
use App\Models\Page;

use function Pest\Laravel\get;

it('toont de over-ons-pagina met de Page-intro en alle familieleden', function () {
    Page::factory()->create([
        'title' => 'Over ons',
        'slug' => 'over-ons',
        'body' => '<p>Wij zijn de familie Westein.</p>',
        'published_at' => now()->subDay(),
    ]);

    FamilyMember::factory()->create(['name' => 'Jan', 'order' => 1]);
    FamilyMember::factory()->create(['name' => 'Marieke', 'order' => 2]);

    get('/over-ons')
        ->assertOk()
        ->assertSee('Over ons')
        ->assertSee('Wij zijn de familie Westein', false)
        ->assertSee('Jan')
        ->assertSee('Marieke');
});

it('linkt elke familielid-kaart naar de auteur-pagina', function () {
    $member = FamilyMember::factory()->create(['name' => 'Sophie']);

    get('/over-ons')
        ->assertOk()
        ->assertSee(route('authors.show', $member), false);
});

it('rendert ook zonder over-ons-Page met een fallback-titel', function () {
    FamilyMember::factory()->create(['name' => 'Tim']);

    get('/over-ons')
        ->assertOk()
        ->assertSee('Over ons')
        ->assertSee('Tim');
});
