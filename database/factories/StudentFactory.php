<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        $nis = fake()->unique()->numerify('2026######');

        return [
            'user_id' => User::factory()->state([
                'role' => 'student',
                'password' => Hash::make($nis),
            ]),
            'nis' => $nis,
            'classroom_id' => Classroom::factory(),
        ];
    }
}

