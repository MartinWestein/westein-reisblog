<?php

namespace Database\Seeders;

use App\Models\FamilyMember;
use Illuminate\Database\Seeder;

class FamilyMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            ['name' => 'Jan Westein', 'role' => 'Vader & hoofdchauffeur', 'order' => 1],
            ['name' => 'Marloes Westein', 'role' => 'Moeder & reisplanner', 'order' => 2],
            ['name' => 'Sophie Westein', 'role' => 'Dochter & fotograaf', 'order' => 3],
            ['name' => 'Tim Westein', 'role' => 'Zoon & avonturier', 'order' => 4],
        ];

        foreach ($members as $data) {
            FamilyMember::firstOrCreate(
                ['name' => $data['name']],
                [
                    'role' => $data['role'],
                    'bio' => fake('nl_NL')->paragraph(3),
                    'order' => $data['order'],
                ]
            );
        }
    }
}
