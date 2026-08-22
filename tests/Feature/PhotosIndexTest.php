<?php

use App\Models\Destination;
use App\Models\Location;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

function locationWithGalleryPhoto(Destination $destination, string $name, string $slug): Location
{
    $location = Location::factory()->for($destination)->create(['name' => $name, 'slug' => $slug]);
    $location->addMedia(UploadedFile::fake()->image($slug.'.jpg', 800, 600))->toMediaCollection('gallery');

    return $location;
}

it('toont de fotogalerij met foto-tegels', function () {
    Storage::fake('public');
    $destination = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    locationWithGalleryPhoto($destination, 'Rome', 'rome');

    get('/fotos')
        ->assertOk()
        ->assertSee('Beeld')
        ->assertSee('Foto van Rome');
});

it('toont alleen bestemmingen met fotos als filter-pill', function () {
    Storage::fake('public');
    $withPhotos = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    locationWithGalleryPhoto($withPhotos, 'Rome', 'rome');

    Destination::factory()->create(['slug' => 'leegland', 'name' => 'Leegland']);

    get('/fotos')
        ->assertOk()
        ->assertSee('Italie')
        ->assertDontSee('Leegland');
});

it('filtert op bestemming en toont dan de locatie-pills', function () {
    Storage::fake('public');
    $italie = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    locationWithGalleryPhoto($italie, 'Rome', 'rome');

    $schotland = Destination::factory()->create(['slug' => 'schotland', 'name' => 'Schotland']);
    locationWithGalleryPhoto($schotland, 'Edinburgh', 'edinburgh');

    get('/fotos?bestemming=italie')
        ->assertOk()
        ->assertSee('Foto van Rome')
        ->assertDontSee('Foto van Edinburgh')
        ->assertSee('locatie=rome', false);
});

it('filtert op een specifieke locatie', function () {
    Storage::fake('public');
    $italie = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    locationWithGalleryPhoto($italie, 'Rome', 'rome');
    locationWithGalleryPhoto($italie, 'Florence', 'florence');

    get('/fotos?bestemming=italie&locatie=rome')
        ->assertOk()
        ->assertSee('Foto van Rome')
        ->assertDontSee('Foto van Florence');
});

it('toont een lege staat zonder fotos', function () {
    get('/fotos')
        ->assertOk()
        ->assertSee('nog geen foto');
});

it('negeert een onbekende bestemming-slug netjes', function () {
    Storage::fake('public');
    $italie = Destination::factory()->create(['slug' => 'italie', 'name' => 'Italie']);
    locationWithGalleryPhoto($italie, 'Rome', 'rome');

    get('/fotos?bestemming=bestaat-niet')
        ->assertOk()
        ->assertSee('Foto van Rome');
});
