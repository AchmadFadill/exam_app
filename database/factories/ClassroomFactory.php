<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Classroom>
 */
class ClassroomFactory extends Factory
{
    public function definition(): array
    {
        $levels = ['X', 'XI', 'XII'];
        $level = fake()->randomElement($levels);

        return [
            'name' => sprintf('%s %s %d', $level, fake()->randomElement(['IPA', 'IPS']), fake()->numberBetween(1, 10)),
            'level' => $level,
            'teacher_id' => null,
        ];
    }
}

