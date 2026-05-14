<?php

use App\Models\Page;
use Illuminate\Http\UploadedFile;

test('page genereert slug uit title', function () {
    $page = Page::create([
        'title' => 'Over de familie Westein',
        'body' => '<p>Hallo.</p>',
    ]);

    expect($page->slug)->toBe('over-de-familie-westein');
});

test('page slug wijzigt niet bij title-update', function () {
    $page = Page::create([
        'title' => 'Originele titel',
        'body' => '<p>Inhoud.</p>',
    ]);
    $originalSlug = $page->slug;

    $page->update(['title' => 'Nieuwe titel']);

    expect($page->fresh()->slug)->toBe($originalSlug);
});

test('published scope filtert pages met published_at in het verleden', function () {
    Page::create(['title' => 'Live', 'body' => 'x', 'published_at' => now()->subDay()]);
    Page::create(['title' => 'Draft', 'body' => 'x', 'published_at' => null]);
    Page::create(['title' => 'Toekomst', 'body' => 'x', 'published_at' => now()->addDay()]);

    expect(Page::published()->count())->toBe(1);
});

test('ordered scope sorteert op order dan title', function () {
    Page::create(['title' => 'Bravo', 'body' => 'x', 'order' => 2]);
    Page::create(['title' => 'Alfa', 'body' => 'x', 'order' => 1]);
    Page::create(['title' => 'Charlie', 'body' => 'x', 'order' => 1]);

    $titles = Page::ordered()->pluck('title')->all();

    expect($titles)->toBe(['Alfa', 'Charlie', 'Bravo']);
});

test('page registreert hero media collection', function () {
    $page = Page::create(['title' => 'Met foto', 'body' => 'x']);

    $page->addMedia(UploadedFile::fake()->image('hero.jpg', 1200, 800))
        ->toMediaCollection('hero');

    expect($page->getMedia('hero'))->toHaveCount(1);
});

test('page hero collection is single file', function () {
    $page = Page::create(['title' => 'Single file', 'body' => 'x']);

    $page->addMedia(UploadedFile::fake()->image('eerste.jpg'))->toMediaCollection('hero');
    $page->addMedia(UploadedFile::fake()->image('tweede.jpg'))->toMediaCollection('hero');

    // singleFile() vervangt automatisch
    expect($page->fresh()->getMedia('hero'))->toHaveCount(1);
});
