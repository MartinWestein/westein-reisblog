<?php

use App\Models\Category;
use App\Models\Destination;
use App\Models\FamilyMember;
use App\Models\Location;
use App\Models\Post;
use App\Models\User;

use function Pest\Laravel\get;

it('toont de auteur-pagina met naam, rol en bio', function () {
    $member = FamilyMember::factory()->create([
        'name' => 'Jan',
        'role' => 'Vader & reisplanner',
        'bio' => 'Jan is de vaste chauffeur van het gezin.',
    ]);

    get(route('authors.show', $member))
        ->assertOk()
        ->assertSee('Jan')
        ->assertSee('Vader & reisplanner')
        ->assertSee('Jan is de vaste chauffeur van het gezin.');
});

it('toont de verhalen van een aan-User-gekoppelde auteur', function () {
    $user = User::factory()->create();
    $member = FamilyMember::factory()->create(['user_id' => $user->id, 'name' => 'Jan']);

    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);

    Post::factory()->published()->create([
        'user_id' => $user->id,
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Onze eerste dag in Rome',
    ]);

    get(route('authors.show', $member))
        ->assertOk()
        ->assertSee('Verhalen van Jan')
        ->assertSee('Onze eerste dag in Rome');
});

it('toont geen verhalen-strook voor een niet-gekoppeld familielid', function () {
    $member = FamilyMember::factory()->create(['user_id' => null, 'name' => 'Sophie']);

    get(route('authors.show', $member))
        ->assertOk()
        ->assertDontSee('Verhalen van Sophie');
});

it('sluit reistips uit van de verhalenlijst van een auteur', function () {
    $tips = Category::factory()->create(['name' => 'Tips']);
    $user = User::factory()->create();
    $member = FamilyMember::factory()->create(['user_id' => $user->id, 'name' => 'Marieke']);

    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    $location = Location::factory()->for($destination)->create(['slug' => 'rome', 'name' => 'Rome']);

    Post::factory()->published()->create([
        'user_id' => $user->id,
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Een gewoon verhaal van Marieke',
    ]);
    $tip = Post::factory()->published()->create([
        'user_id' => $user->id,
        'destination_id' => $destination->id,
        'location_id' => $location->id,
        'title' => 'Een reistip van Marieke',
    ]);
    $tip->categories()->attach($tips);

    get(route('authors.show', $member))
        ->assertOk()
        ->assertSee('Een gewoon verhaal van Marieke')
        ->assertDontSee('Een reistip van Marieke');
});

it('geeft 404 voor een onbekende auteur-slug', function () {
    get('/auteurs/bestaat-niet')->assertNotFound();
});
