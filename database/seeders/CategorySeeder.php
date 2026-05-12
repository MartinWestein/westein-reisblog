<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Verslag',    'order' => 1, 'description' => 'Reisverslagen en dagelijkse belevenissen onderweg.'],
            ['name' => 'Tips',       'order' => 2, 'description' => 'Praktische reistips voor families en mede-reizigers.'],
            ['name' => 'Eten',       'order' => 3, 'description' => 'Eten, restaurants en culinaire ontdekkingen.'],
            ['name' => 'Activiteit', 'order' => 4, 'description' => 'Activiteiten, bezienswaardigheden en uitstapjes.'],
        ];

        foreach ($categories as $data) {
            Category::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
