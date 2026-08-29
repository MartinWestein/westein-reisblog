<?php

use App\Models\Category;
use App\Models\Destination;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Spatie\Permission\Models\Role;

it('seedt rollen, categorieën en een geverifieerd admin-account zonder demo-content', function () {
    $this->seed(ProductionSeeder::class);

    // Rollen
    expect(Role::whereIn('name', ['admin', 'editor', 'auteur', 'lid'])->count())->toBe(4);

    // Structurele categorieën (incl. Tips, nodig voor de reistip-URL's)
    expect(Category::whereIn('name', ['Verslag', 'Tips', 'Eten', 'Activiteit'])->count())->toBe(4);

    // Admin-account
    $admin = User::where('email', 'reizen@ml-westein.nl')->first();
    expect($admin)->not->toBeNull();
    expect($admin->name)->toBe('Martin');
    expect($admin->hasRole('admin'))->toBeTrue();
    expect($admin->hasVerifiedEmail())->toBeTrue();

    // Geen demo-content
    expect(Destination::count())->toBe(0);
    expect(Post::count())->toBe(0);
});

it('is idempotent — twee keer draaien geeft geen dubbel admin-account', function () {
    $this->seed(ProductionSeeder::class);
    $this->seed(ProductionSeeder::class);

    expect(User::where('email', 'reizen@ml-westein.nl')->count())->toBe(1);
});
