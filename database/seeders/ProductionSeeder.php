<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductionSeeder extends Seeder
{
    /**
     * Productie-seed: rollen/permissies + basiscategorieën + het admin-account.
     * Draait bewust GEEN DemoContentSeeder — productie start met echte content
     * via de admin.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            CategorySeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'reizen@ml-westein.nl'],
            [
                'name' => 'Martin',
                'password' => Str::random(48), // onbruikbaar — zet via "wachtwoord vergeten"
            ],
        );

        if (! $admin->hasVerifiedEmail()) {
            $admin->forceFill(['email_verified_at' => now()])->save();
        }

        $admin->assignRole('admin');
    }
}
