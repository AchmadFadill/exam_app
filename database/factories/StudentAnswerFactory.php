<?php

namespace Database\Factories;

use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentAnswer>
 */
class StudentAnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_attempt_id' => ExamAttempt::factory(),
            'question_id' => Question::factory(),
            'answer' => fake()->sentence(8),
            'selected_option_id' => QuestionOption::factory(),
            'is_correct' => fake()->boolean(),
            'score_awarded' => fake()->numberBetween(0, 5),
            'teacher_feedback' => fake()->optional()->sentence(8),
        ];
    }
}

