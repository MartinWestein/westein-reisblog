<?php

namespace Database\Factories;

use App\Models\FamilyMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FamilyMember>
 */
class FamilyMemberFactory extends Factory
{
    protected $model = FamilyMember::class;

    public function definition(): array
    {
        $name = fake()->firstName();

        return [
            'user_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'role' => fake()->randomElement(['Vader', 'Moeder', 'Dochter', 'Zoon', 'Reisplanner', 'Fotograaf']),
            'bio' => fake()->paragraph(3),
            'order' => 0,
        ];
    }
}
