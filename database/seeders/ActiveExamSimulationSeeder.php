<?php

namespace Database\Seeders;

use App\Enums\ExamAttemptStatus;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\StudentAnswer;
use App\Services\ScoringService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ActiveExamSimulationSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $exam = $this->resolveActiveExam($now);

        if (!$exam) {
            $this->command?->warn('Tidak ada exam aktif/running saat ini.');
            return;
        }

        $classrooms = $exam->classrooms()->select('classrooms.id', 'classrooms.name')->get();
        if ($classrooms->isEmpty()) {
            $this->command?->warn("Exam #{$exam->id} tidak memiliki kelas terdaftar.");
            return;
        }

        $students = Student::query()
            ->whereIn('classroom_id', $classrooms->pluck('id'))
            ->orderBy('id')
            ->limit(50)
            ->get();

        if ($students->isEmpty()) {
            $this->command?->warn("Tidak ada siswa pada kelas exam #{$exam->id}.");
            return;
        }

        $questions = $exam->questions()
            ->with('options:id,question_id,label,text,is_correct')
            ->get(['questions.id', 'questions.type', 'questions.answer_key']);

        if ($questions->isEmpty()) {
            $this->command?->warn("Exam #{$exam->id} tidak memiliki soal.");
            return;
        }

        $questionIds = $questions->pluck('id')->all();
        $hasEssay = $questions->contains(fn ($q) => $q->type === 'essay');

        $totalStudents = $students->count();
        $blankCount = (int) round($totalStudents * 0.10);
        $randomCount = (int) round($totalStudents * 0.20);
        $inProgressCount = max(1, (int) round($totalStudents * 0.20));
        $perfectCount = max(0, $totalStudents - $blankCount - $randomCount - $inProgressCount);

        $students = $students->shuffle()->values();
        $inProgressStudentIds = $students->take($inProgressCount)->pluck('id')->all();
        $blankStudentIds = $students->slice($inProgressCount, $blankCount)->pluck('id')->all();
        $randomStudentIds = $students->slice($inProgressCount + $blankCount, $randomCount)->pluck('id')->all();

        $this->command?->info("Simulating exam: #{$exam->id} - {$exam->name}");
        $this->command?->info('Kelas: ' . $classrooms->pluck('name')->join(', '));
        $this->command?->info("Total siswa target: {$totalStudents} (InProgress: {$inProgressCount}, Perfect: {$perfectCount}, Random: {$randomCount}, Blank: {$blankCount})");

        $scoringService = app(ScoringService::class);
        $simulatedCount = 0;

        foreach ($students as $student) {
            $strategy = in_array($student->id, $blankStudentIds, true)
                ? 'blank'
                : (in_array($student->id, $randomStudentIds, true)
                    ? 'random'
                    : (in_array($student->id, $inProgressStudentIds, true) ? 'in_progress' : 'perfect'));

            $startedAt = (clone $now)->subMinutes(random_int(1, 60))->subSeconds(random_int(0, 59));
            $submittedAt = (clone $startedAt)->addMinutes(random_int(5, 55));
            if ($submittedAt->greaterThan($now)) {
                $submittedAt = (clone $now)->subSeconds(random_int(0, 30));
            }

            $attempt = ExamAttempt::firstOrCreate(
                [
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                ],
                [
                    'started_at' => $startedAt,
                    'status' => ExamAttemptStatus::InProgress->value,
                ]
            );

            $attempt->forceFill([
                'started_at' => $startedAt,
                'last_seen_at' => $submittedAt,
                'submitted_at' => null,
                'status' => ExamAttemptStatus::InProgress->value,
                'tab_switches' => random_int(0, 2),
            ])->save();

            // Keep answers consistent only for this exam's assigned questions.
            $attempt->answers()->whereNotIn('question_id', $questionIds)->delete();

            if ($strategy === 'blank') {
                $attempt->answers()->whereIn('question_id', $questionIds)->delete();
            } elseif ($strategy === 'in_progress') {
                // Simulate ongoing work: answer only some questions and keep the attempt open.
                $partialQuestions = $questions->shuffle()->take(max(1, (int) floor($questions->count() * 0.5)));
                $keepIds = $partialQuestions->pluck('id')->all();

                $attempt->answers()->whereNotIn('question_id', $keepIds)->delete();

                foreach ($partialQuestions as $question) {
                    $answerValue = $this->buildAnswerValue($question, 'random');
                    $scored = $scoringService->scoreSingleAnswer($exam, $question, $answerValue);

                    StudentAnswer::updateOrCreate(
                        [
                            'exam_attempt_id' => $attempt->id,
                            'question_id' => (int) $question->id,
                        ],
                        $scored
                    );
                }
            } else {
                foreach ($questions as $question) {
                    $answerValue = $this->buildAnswerValue($question, $strategy);
                    $scored = $scoringService->scoreSingleAnswer($exam, $question, $answerValue);

                    StudentAnswer::updateOrCreate(
                        [
                            'exam_attempt_id' => $attempt->id,
                            'question_id' => (int) $question->id,
                        ],
                        $scored
                    );
                }
            }

            $summary = $scoringService->recalculateAttempt($exam, $attempt);

            if ($strategy === 'in_progress') {
                $attempt->update([
                    'submitted_at' => null,
                    'last_seen_at' => $now,
                    'status' => ExamAttemptStatus::InProgress,
                    'total_score' => null,
                    'percentage' => null,
                    'passed' => null,
                ]);
            } else {
                $attempt->update([
                    'submitted_at' => $submittedAt,
                    'last_seen_at' => $submittedAt,
                    'status' => $hasEssay ? ExamAttemptStatus::Submitted : ExamAttemptStatus::Graded,
                    'total_score' => $summary['total_score'],
                    'percentage' => $summary['percentage'],
                    'passed' => $summary['passed'],
                ]);
            }

            $simulatedCount++;
        }

        $this->command?->info("Selesai simulasi {$simulatedCount} siswa untuk exam #{$exam->id}.");
    }

    private function resolveActiveExam(Carbon $now): ?Exam
    {
        $time = $now->format('H:i:s');
        $today = $now->toDateString();

        $query = Exam::query()
            ->with('classrooms:id,name')
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->orderByDesc('id');

        $activeByStatusAndWindow = (clone $query)
            ->whereIn('status', ['active', 'running'])
            ->whereDate('date', $today)
            ->whereTime('start_time', '<=', $time)
            ->whereTime('end_time', '>=', $time)
            ->first();

        if ($activeByStatusAndWindow) {
            return $activeByStatusAndWindow;
        }

        $activeByStatus = (clone $query)
            ->whereIn('status', ['active', 'running'])
            ->first();

        if ($activeByStatus) {
            return $activeByStatus;
        }

        return (clone $query)
            ->whereDate('date', $today)
            ->whereTime('start_time', '<=', $time)
            ->whereTime('end_time', '>=', $time)
            ->first();
    }

    private function buildAnswerValue($question, string $strategy): mixed
    {
        if ($question->type === 'essay') {
            if ($strategy === 'random') {
                return random_int(1, 100) <= 40 ? '' : ('Jawaban acak ' . fake()->sentence(8));
            }

            return trim((string) ($question->answer_key ?? '')) !== ''
                ? (string) $question->answer_key
                : ('Jawaban lengkap ' . fake()->sentence(10));
        }

        $options = $question->options->values();
        if ($options->isEmpty()) {
            return null;
        }

        if ($strategy === 'perfect') {
            $correctOption = $options->firstWhere('is_correct', true) ?? $options->first();
            return $correctOption?->id;
        }

        return $options->random()->id;
    }
}
