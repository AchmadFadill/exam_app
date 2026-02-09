<?php

namespace Database\Factories;

use App\Enums\ExamAttemptStatus;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExamAttempt>
 */
class ExamAttemptFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement([
            ExamAttemptStatus::InProgress->value,
            ExamAttemptStatus::Submitted->value,
            ExamAttemptStatus::Graded->value,
        ]);

        $startedAt = fake()->dateTimeBetween('-3 hours', '-10 minutes');
        $submittedAt = in_array($status, [ExamAttemptStatus::Submitted->value, ExamAttemptStatus::Graded->value], true)
            ? fake()->dateTimeBetween($startedAt, 'now')
            : null;

        return [
            'exam_id' => Exam::factory(),
            'student_id' => Student::factory(),
            'started_at' => $startedAt,
            'last_seen_at' => fake()->dateTimeBetween('-2 hours', 'now'),
            'submitted_at' => $submittedAt,
            'status' => $status,
            'tab_switches' => fake()->numberBetween(0, 4),
            'total_score' => $submittedAt ? fake()->numberBetween(0, 100) : null,
            'percentage' => $submittedAt ? fake()->randomFloat(2, 0, 100) : null,
            'passed' => $submittedAt ? fake()->boolean() : null,
        ];
    }
}

