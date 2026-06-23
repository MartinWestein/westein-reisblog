<?php

use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;

beforeEach(function () {
    Storage::fake('public');
    Queue::fake();
});

test('Post registreert featured (single) en inline_images (multi) collecties', function () {
    $post = Post::factory()->create();

    $collections = $post->getRegisteredMediaCollections();

    expect($collections)->toHaveCount(2);

    $featured = $collections->firstWhere('name', 'featured');
    expect($featured)->not->toBeNull()
        ->and($featured->singleFile)->toBeTrue();

    $inline = $collections->firstWhere('name', 'inline_images');
    expect($inline)->not->toBeNull()
        ->and($inline->singleFile)->toBeFalse();
});

test('Location registreert gallery collectie als multi-file', function () {
    $location = Location::factory()->create();

    $collections = $location->getRegisteredMediaCollections();

    expect($collections)->toHaveCount(1)
        ->and($collections->first()->name)->toBe('gallery')
        ->and($collections->first()->singleFile)->toBeFalse();
});

test('Destination registreert hero (single) en gallery (multi)', function () {
    $destination = Destination::factory()->create();

    $collections = $destination->getRegisteredMediaCollections();

    expect($collections)->toHaveCount(2);

    $hero = $collections->firstWhere('name', 'hero');
    $gallery = $collections->firstWhere('name', 'gallery');

    expect($hero->singleFile)->toBeTrue()
        ->and($gallery->singleFile)->toBeFalse();
});

test('User registreert avatar collectie als single-file', function () {
    $user = User::factory()->create();

    $collections = $user->getRegisteredMediaCollections();

    expect($collections)->toHaveCount(1)
        ->and($collections->first()->name)->toBe('avatar')
        ->and($collections->first()->singleFile)->toBeTrue();
});

test('Single-file collectie vervangt eerdere upload', function () {
    $post = Post::factory()->create();

    $post->addMedia(UploadedFile::fake()->image('first.jpg'))
        ->toMediaCollection('featured');
    $post->addMedia(UploadedFile::fake()->image('second.jpg'))
        ->toMediaCollection('featured');

    expect($post->getMedia('featured'))->toHaveCount(1)
        ->and($post->getFirstMedia('featured')->file_name)->toBe('second.jpg');
});

test('Multi-file collectie behoudt meerdere uploads', function () {
    $location = Location::factory()->create();

    $location->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection('gallery');
    $location->addMedia(UploadedFile::fake()->image('two.jpg'))->toMediaCollection('gallery');
    $location->addMedia(UploadedFile::fake()->image('three.jpg'))->toMediaCollection('gallery');

    expect($location->getMedia('gallery'))->toHaveCount(3);
});

test('Upload op Post featured draait conversies synchroon (non-queued, beslissing 4.10)', function () {
    $post = Post::factory()->create();
    $post->addMedia(UploadedFile::fake()->image('foto.jpg', 1200, 800))
        ->toMediaCollection('featured');

    // Per RegistersMediaConversions::registerWebpConversion() → ->nonQueued()
    // Conversies draaien direct in dezelfde request en gaan NIET via de queue.
    Queue::assertNotPushed(PerformConversionsJob::class);
});

test('Niet-toegestane MIME-type wordt geweigerd', function () {
    $post = Post::factory()->create();

    expect(fn () => $post->addMedia(UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'))
        ->toMediaCollection('featured'))
        ->toThrow(FileCannotBeAdded::class);
});
