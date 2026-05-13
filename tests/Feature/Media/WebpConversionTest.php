<?php

use App\Models\Destination;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    // Sync queue: conversies draaien direct, geen Queue::fake().
    Config::set('queue.default', 'sync');
    Config::set('media-library.queue_connection_name', 'sync');
});

test('Avatar-upload produceert daadwerkelijk twee WebP-bestanden op disk', function () {
    $user = User::factory()->create();

    $media = $user->addMedia(UploadedFile::fake()->image('me.jpg', 800, 800))
        ->toMediaCollection('avatar');

    // Origineel blijft staan
    expect(Storage::disk('public')->exists($media->id.'/me.jpg'))->toBeTrue();

    // Twee conversies bestaan
    $thumbPath = $media->id.'/conversions/me-thumb.webp';
    $mediumPath = $media->id.'/conversions/me-medium.webp';

    expect(Storage::disk('public')->exists($thumbPath))->toBeTrue()
        ->and(Storage::disk('public')->exists($mediumPath))->toBeTrue();

    // En het zijn écht WebP-bestanden (magic bytes "RIFF....WEBP")
    $thumbBytes = Storage::disk('public')->get($thumbPath);
    expect(substr($thumbBytes, 0, 4))->toBe('RIFF')
        ->and(substr($thumbBytes, 8, 4))->toBe('WEBP');
});

test('Destination hero upload genereert medium en large, géén thumb (filter werkt)', function () {
    $destination = Destination::factory()->create();

    $media = $destination->addMedia(UploadedFile::fake()->image('hero.jpg', 2000, 1200))
        ->toMediaCollection('hero');

    // Hero-conversies moeten bestaan
    expect(Storage::disk('public')->exists($media->id.'/conversions/hero-medium.webp'))->toBeTrue()
        ->and(Storage::disk('public')->exists($media->id.'/conversions/hero-large.webp'))->toBeTrue();

    // Thumb hoort er NIET te zijn (filter via performOnCollections)
    expect(Storage::disk('public')->exists($media->id.'/conversions/hero-thumb.webp'))->toBeFalse();
});
