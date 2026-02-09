<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'subject_id' => Subject::factory(),
            'title' => 'Paket ' . fake()->bothify('??-##'),
            'type' => fake()->randomElement(['multiple_choice', 'essay']),
            'text' => '<p>' . fake()->sentence(12) . '</p>',
            'image_path' => null,
            'explanation' => fake()->sentence(10),
            'answer_key' => fake()->sentence(6),
            'score' => fake()->numberBetween(1, 5),
        ];
    }

    public function dirtyHtml(): static
    {
        return $this->state(fn () => [
            'text' => "<p>Perhatikan gambar berikut.</p><script>alert('xss')</script><iframe src=\"https://example.com\"></iframe><b>teks tebal</b>",
        ]);
    }
}

