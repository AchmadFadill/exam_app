<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exam>
 */
class ExamFactory extends Factory
{
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-2 days', '+2 days');

        return [
            'teacher_id' => Teacher::factory(),
            'subject_id' => Subject::factory(),
            'name' => 'Ujian ' . fake()->sentence(3),
            'date' => $date->format('Y-m-d'),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'duration_minutes' => 120,
            'token' => strtoupper(fake()->bothify('??##??')),
            'passing_grade' => 70,
            'default_score' => 2,
            'shuffle_questions' => fake()->boolean(),
            'shuffle_answers' => fake()->boolean(),
            'enable_tab_tolerance' => fake()->boolean(),
            'tab_tolerance' => fake()->numberBetween(0, 5),
            'status' => 'scheduled',
        ];
    }
}

