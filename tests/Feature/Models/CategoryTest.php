<?php

use App\Models\Category;
use Database\Seeders\CategorySeeder;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

test('slug wordt automatisch gegenereerd uit naam', function () {
    $category = Category::create([
        'name' => 'Reistips voor kampeerders',
    ]);

    expect($category->slug)->toBe('reistips-voor-kampeerders');
});

test('slug blijft uniek bij dubbele naam', function () {
    Category::create(['name' => 'Eten']);
    $second = Category::create(['name' => 'Eten']);

    expect($second->slug)->not->toBe('eten')
        ->and($second->slug)->toStartWith('eten-');
});

test('route key is slug', function () {
    $category = Category::create(['name' => 'Activiteit']);

    expect($category->getRouteKeyName())->toBe('slug')
        ->and($category->getRouteKey())->toBe('activiteit');
});

test('seeder maakt vier categorieen', function () {
    seed(CategorySeeder::class);

    assertDatabaseCount('categories', 4);
    assertDatabaseHas('categories', ['name' => 'Verslag', 'order' => 1]);
    assertDatabaseHas('categories', ['name' => 'Tips', 'order' => 2]);
    assertDatabaseHas('categories', ['name' => 'Eten', 'order' => 3]);
    assertDatabaseHas('categories', ['name' => 'Activiteit', 'order' => 4]);
});

test('seeder is idempotent', function () {
    seed(CategorySeeder::class);
    seed(CategorySeeder::class);

    assertDatabaseCount('categories', 4);
});
