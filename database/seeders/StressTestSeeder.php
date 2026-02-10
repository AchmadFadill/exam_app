<?php

namespace Database\Seeders;

use App\Enums\ExamAttemptStatus;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StressTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::disableQueryLog();

        User::updateOrCreate(
            ['email' => 'admin@smait-baitulmuslim.sch.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $subjects = Subject::factory()->count(50)->create();

        $teachers = Teacher::factory()->count(100)->create();
        foreach ($teachers as $teacher) {
            $teacher->subjects()->sync(
                $subjects->random(random_int(1, 3))->pluck('id')->all()
            );
        }

        $classrooms = Classroom::factory()->count(30)->create();

        $students = collect();
        for ($i = 1; $i <= 1500; $i++) {
            $classroom = $classrooms->random();
            $nis = str_pad((string) (2026000000 + $i), 10, '0', STR_PAD_LEFT);

            $user = User::create([
                'name' => 'Student ' . $i,
                'email' => "student{$i}@load.test",
                'password' => Hash::make($nis),
                'role' => 'student',
            ]);

            $students->push(Student::create([
                'user_id' => $user->id,
                'nis' => $nis,
                'classroom_id' => $classroom->id,
            ]));
        }

        $examQuestionBank = [];

        for ($examIndex = 1; $examIndex <= 200; $examIndex++) {
            $teacher = $teachers->random();
            $subjectId = $teacher->subjects()->inRandomOrder()->value('subjects.id') ?? $subjects->random()->id;

            $exam = Exam::create([
                'teacher_id' => $teacher->id,
                'subject_id' => $subjectId,
                'name' => "Stress Exam {$examIndex}",
                'date' => now()->toDateString(),
                'start_time' => '07:00:00',
                'end_time' => '23:00:00',
                'duration_minutes' => 120,
                'token' => strtoupper(substr(md5("stress-{$examIndex}-" . microtime(true)), 0, 6)),
                'passing_grade' => 70,
                'default_score' => 2,
                'shuffle_questions' => (bool) random_int(0, 1),
                'shuffle_answers' => (bool) random_int(0, 1),
                'enable_tab_tolerance' => (bool) random_int(0, 1),
                'tab_tolerance' => random_int(1, 5),
                'status' => 'scheduled',
            ]);

            $exam->classrooms()->sync(
                $classrooms->random(random_int(1, 3))->pluck('id')->all()
            );

            $bank = [];
            $pivotRows = [];

            for ($q = 1; $q <= 50; $q++) {
                $isEssay = ($q % 5 === 0);
                $isDirtyHtml = random_int(1, 100) <= 10;

                $questionText = $isDirtyHtml
                    ? "<p>Soal {$q} untuk exam {$examIndex}</p><script>alert('xss')</script><iframe src=\"https://evil.test\"></iframe><b>bold</b><i>italic</i><img src=\"https://picsum.photos/200\">"
                    : '<p>' . fake()->sentence(15) . '</p>';

                $question = Question::create([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subjectId,
                    'title' => "Stress Group {$examIndex}",
                    'type' => $isEssay ? 'essay' : 'multiple_choice',
                    'text' => $questionText,
                    'explanation' => fake()->sentence(12),
                    'answer_key' => $isEssay ? fake()->sentence(8) : null,
                    'score' => 2,
                ]);

                $optionIds = [];
                $correctOptionId = null;
                if (!$isEssay) {
                    $correctLabel = fake()->randomElement(['A', 'B', 'C', 'D']);
                    foreach (['A', 'B', 'C', 'D'] as $label) {
                        $id = DB::table('question_options')->insertGetId([
                            'question_id' => $question->id,
                            'label' => $label,
                            'text' => fake()->sentence(4),
                            'is_correct' => $label === $correctLabel,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $optionIds[] = $id;
                        if ($label === $correctLabel) {
                            $correctOptionId = $id;
                        }
                    }
                }

                $pivotRows[$question->id] = ['order' => $q, 'score' => 2];
                $bank[] = [
                    'question_id' => $question->id,
                    'type' => $question->type,
                    'score' => 2,
                    'correct_option_id' => $correctOptionId,
                    'option_ids' => $optionIds,
                ];
            }

            $exam->questions()->sync($pivotRows);
            $examQuestionBank[$exam->id] = $bank;
        }

        $studentIds = $students->pluck('id')->all();
        $examIds = array_keys($examQuestionBank);
        $pairKeys = [];
        $attempts = [];

        for ($i = 1; $i <= 5000; $i++) {
            do {
                $examId = $examIds[array_rand($examIds)];
                $studentId = $studentIds[array_rand($studentIds)];
                $pair = "{$examId}:{$studentId}";
            } while (isset($pairKeys[$pair]));
            $pairKeys[$pair] = true;

            $status = $this->randomStatus();
            $startedAt = Carbon::now()->subMinutes(random_int(5, 180));
            $submittedAt = in_array($status, [ExamAttemptStatus::Submitted->value, ExamAttemptStatus::Graded->value], true)
                ? (clone $startedAt)->addMinutes(random_int(20, 120))
                : null;

            $attempt = ExamAttempt::create([
                'exam_id' => $examId,
                'student_id' => $studentId,
                'started_at' => $startedAt,
                'last_seen_at' => Carbon::now()->subSeconds(random_int(0, 7200)),
                'submitted_at' => $submittedAt,
                'status' => $status,
                'tab_switches' => random_int(0, 6),
            ]);

            $attempts[] = $attempt;
        }

        $answerBatch = [];
        foreach ($attempts as $attempt) {
            $attemptStatus = $attempt->status instanceof ExamAttemptStatus
                ? $attempt->status->value
                : (string) $attempt->status;

            if (!in_array($attemptStatus, [ExamAttemptStatus::Submitted->value, ExamAttemptStatus::Graded->value], true)) {
                continue;
            }

            $totalScore = 0;
            $maxScore = 0;
            foreach ($examQuestionBank[$attempt->exam_id] as $meta) {
                $maxScore += $meta['score'];
                if ($meta['type'] === 'essay') {
                    $awarded = random_int(0, $meta['score']);
                    $totalScore += $awarded;
                    $answerBatch[] = [
                        'exam_attempt_id' => $attempt->id,
                        'question_id' => $meta['question_id'],
                        'selected_option_id' => null,
                        'answer' => fake()->sentence(14),
                        'is_correct' => null,
                        'score_awarded' => $awarded,
                        'teacher_feedback' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } else {
                    $isCorrect = random_int(1, 100) <= 65;
                    $selected = $isCorrect
                        ? $meta['correct_option_id']
                        : $meta['option_ids'][array_rand($meta['option_ids'])];
                    if (!$isCorrect && $selected === $meta['correct_option_id']) {
                        $selected = $meta['option_ids'][0] === $meta['correct_option_id']
                            ? $meta['option_ids'][1]
                            : $meta['option_ids'][0];
                    }

                    $awarded = ($selected === $meta['correct_option_id']) ? $meta['score'] : 0;
                    $totalScore += $awarded;
                    $answerBatch[] = [
                        'exam_attempt_id' => $attempt->id,
                        'question_id' => $meta['question_id'],
                        'selected_option_id' => $selected,
                        'answer' => (string) $selected,
                        'is_correct' => $selected === $meta['correct_option_id'],
                        'score_awarded' => $awarded,
                        'teacher_feedback' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (count($answerBatch) >= 2000) {
                    DB::table('student_answers')->insert($answerBatch);
                    $answerBatch = [];
                }
            }

            $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;
            $attempt->update([
                'total_score' => $totalScore,
                'percentage' => $percentage,
                'passed' => $percentage >= 70,
            ]);
        }

        if ($answerBatch !== []) {
            DB::table('student_answers')->insert($answerBatch);
        }

        $this->command?->info('StressTestSeeder done: 50 subjects, 100 teachers, 30 classes, 1500 students, 200 exams, 5000 attempts.');
    }

    private function randomStatus(): string
    {
        $roll = random_int(1, 100);

        if ($roll <= 30) {
            return ExamAttemptStatus::InProgress->value;
        }

        if ($roll <= 70) {
            return ExamAttemptStatus::Submitted->value;
        }

        return ExamAttemptStatus::Graded->value;
    }
}
