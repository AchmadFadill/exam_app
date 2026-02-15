<?php

namespace Database\Seeders;

use App\Enums\ExamAttemptStatus;
use App\Models\Exam;
use App\Models\ExamActivity;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\StudentAnswer;
use App\Services\ScoringService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MassExamViolationSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $exams = $this->resolveActiveExams($now);

        if ($exams->isEmpty()) {
            $this->command?->warn('Tidak ditemukan exam aktif/running.');
            return;
        }

        $this->command?->info("Ditemukan {$exams->count()} exam aktif. Memulai simulasi mass violation...");

        foreach ($exams as $exam) {
            $this->seedExam($exam, $now);
        }

        $this->command?->info('MassExamViolationSeeder selesai untuk semua exam aktif.');
    }

    private function seedExam(Exam $exam, Carbon $now): void
    {
        $classrooms = $exam->classrooms()->select('classrooms.id', 'classrooms.name')->get();
        if ($classrooms->isEmpty()) {
            $this->command?->warn("Exam #{$exam->id} dilewati: tidak memiliki kelas terdaftar.");
            return;
        }

        $students = Student::query()
            ->whereIn('classroom_id', $classrooms->pluck('id'))
            ->with('user:id')
            ->orderBy('id')
            ->get();

        if ($students->isEmpty()) {
            $this->command?->warn("Exam #{$exam->id} dilewati: tidak ada siswa pada kelas terdaftar.");
            return;
        }

        $questions = $exam->questions()
            ->with('options:id,question_id,label,text,is_correct')
            ->get(['questions.id', 'questions.type', 'questions.answer_key']);

        if ($questions->isEmpty()) {
            $this->command?->warn("Exam #{$exam->id} dilewati: tidak memiliki soal.");
            return;
        }

        $questionIds = $questions->pluck('id')->all();
        $hasEssay = $questions->contains(fn ($q) => $q->type === 'essay');
        $distribution = $this->buildDistribution($students->count());
        $scenarioMap = $this->assignScenarios($students->pluck('id')->all(), $distribution);

        $this->command?->info("Target exam: #{$exam->id} - {$exam->name}");
        $this->command?->info('Kelas: ' . $classrooms->pluck('name')->join(', '));
        $this->command?->info('Total siswa: ' . $students->count());
        $this->command?->info("Distribusi => A: {$distribution['A']}, B: {$distribution['B']}, C: {$distribution['C']}, D: {$distribution['D']}");

        $scoringService = app(ScoringService::class);

        DB::transaction(function () use (
            $students,
            $scenarioMap,
            $exam,
            $questions,
            $questionIds,
            $hasEssay,
            $now,
            $scoringService
        ) {
            foreach ($students as $student) {
                $startedAt = (clone $now)->subMinutes(random_int(5, 60))->subSeconds(random_int(0, 59));
                $scenario = $scenarioMap[$student->id] ?? 'A';

                $attempt = ExamAttempt::firstOrCreate(
                    ['exam_id' => $exam->id, 'student_id' => $student->id],
                    ['started_at' => $startedAt, 'status' => ExamAttemptStatus::InProgress->value]
                );

                $this->resetAttemptData($attempt, $questionIds);

                match ($scenario) {
                    'A' => $this->applyScenarioA($exam, $attempt, $questions, $hasEssay, $now, $scoringService),
                    'B' => $this->applyScenarioB($exam, $attempt, $student->user_id, $questions, $now, $scoringService),
                    'C' => $this->applyScenarioC($exam, $attempt, $student->user_id, $questions, $now),
                    'D' => $this->applyScenarioD($attempt, $now),
                    default => $this->applyScenarioA($exam, $attempt, $questions, $hasEssay, $now, $scoringService),
                };
            }
        });

        $this->command?->info("Selesai exam #{$exam->id} ({$students->count()} siswa).");
    }

    private function applyScenarioA(
        Exam $exam,
        ExamAttempt $attempt,
        $questions,
        bool $hasEssay,
        Carbon $now,
        ScoringService $scoringService
    ): void {
        $allCorrect = random_int(1, 100) <= 70;

        foreach ($questions as $question) {
            $answerValue = $this->buildAnswerValue($question, $allCorrect ? 'perfect' : 'random');
            $scored = $scoringService->scoreSingleAnswer($exam, $question, $answerValue);

            StudentAnswer::updateOrCreate(
                ['exam_attempt_id' => $attempt->id, 'question_id' => (int) $question->id],
                $scored
            );
        }

        $summary = $scoringService->recalculateAttempt($exam, $attempt);
        $submittedAt = (clone $now)->subMinutes(random_int(1, 5));

        $attempt->update([
            'submitted_at' => $submittedAt,
            'last_seen_at' => $submittedAt,
            'status' => $hasEssay ? ExamAttemptStatus::Submitted : ExamAttemptStatus::Graded,
            'tab_switches' => 0,
            'total_score' => $summary['total_score'],
            'percentage' => $summary['percentage'],
            'passed' => $summary['passed'],
        ]);
    }

    private function applyScenarioB(
        Exam $exam,
        ExamAttempt $attempt,
        int $userId,
        $questions,
        Carbon $now,
        ScoringService $scoringService
    ): void {
        foreach ($questions as $question) {
            $answerValue = $this->buildAnswerValue($question, 'random');
            $scored = $scoringService->scoreSingleAnswer($exam, $question, $answerValue);

            StudentAnswer::updateOrCreate(
                ['exam_attempt_id' => $attempt->id, 'question_id' => (int) $question->id],
                $scored
            );
        }

        $summary = $scoringService->recalculateAttempt($exam, $attempt);
        $submittedAt = (clone $now)->subMinutes(random_int(1, 6));
        $violationCount = random_int(3, 5);

        $attempt->update([
            'submitted_at' => $submittedAt,
            'last_seen_at' => $submittedAt,
            'status' => ExamAttemptStatus::Submitted,
            'tab_switches' => $violationCount,
            'total_score' => $summary['total_score'],
            'percentage' => $summary['percentage'],
            'passed' => $summary['passed'],
        ]);

        $this->createViolations($exam->id, $attempt->id, $userId, $violationCount, ['tab_switch', 'pindah_tab'], 'tab');
    }

    private function applyScenarioC(Exam $exam, ExamAttempt $attempt, int $userId, $questions, Carbon $now): void
    {
        $answerable = max(1, (int) floor($questions->count() * 0.5));
        $answeredIds = $questions->shuffle()->take($answerable)->pluck('id')->all();

        foreach ($questions as $question) {
            if (!in_array((int) $question->id, $answeredIds, true)) {
                continue;
            }

            $answerValue = $this->buildAnswerValue($question, 'random');
            $scored = app(ScoringService::class)->scoreSingleAnswer($exam, $question, $answerValue);

            StudentAnswer::updateOrCreate(
                ['exam_attempt_id' => $attempt->id, 'question_id' => (int) $question->id],
                $scored
            );
        }

        $lastSeen = (clone $now)->subMinutes(random_int(6, 15));

        $attempt->update([
            'submitted_at' => null,
            'last_seen_at' => $lastSeen,
            'status' => ExamAttemptStatus::Ongoing,
            'tab_switches' => random_int(4, 7),
            'total_score' => null,
            'percentage' => null,
            'passed' => null,
        ]);

        $this->createViolations($exam->id, $attempt->id, $userId, random_int(3, 5), ['keluar_fullscreen', 'exit_fullscreen', 'fullscreen_exit'], 'fullscreen');
    }

    private function applyScenarioD(ExamAttempt $attempt, Carbon $now): void
    {
        $attempt->update([
            'submitted_at' => null,
            'last_seen_at' => (clone $now)->subMinutes(30),
            'status' => ExamAttemptStatus::Ongoing,
            'tab_switches' => 0,
            'total_score' => null,
            'percentage' => null,
            'passed' => null,
        ]);
    }

    private function createViolations(
        int $examId,
        int $attemptId,
        int $userId,
        int $count,
        array $types,
        string $mode
    ): void {
        $rows = [];
        $base = now()->subMinutes(random_int(10, 45));

        for ($i = 1; $i <= $count; $i++) {
            $type = $types[array_rand($types)];
            $minute = random_int(3, 50);
            $message = $mode === 'tab'
                ? "Siswa terdeteksi membuka tab lain pada menit ke-{$minute}."
                : "Siswa terdeteksi keluar fullscreen pada menit ke-{$minute}.";

            $timestamp = (clone $base)->addMinutes($i)->addSeconds(random_int(0, 59));

            $rows[] = [
                'user_id' => $userId,
                'exam_id' => $examId,
                'exam_attempt_id' => $attemptId,
                'type' => $type,
                'severity' => 'warning',
                'message' => $message,
                'metadata' => json_encode(['minute' => $minute], JSON_THROW_ON_ERROR),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::table('exam_activities')->insert($rows);
    }

    private function buildAnswerValue($question, string $mode): mixed
    {
        if ($question->type === 'essay') {
            if ($mode === 'perfect') {
                $key = trim((string) ($question->answer_key ?? ''));
                return $key !== '' ? $key : ('Penjelasan lengkap: ' . fake()->sentence(10));
            }

            return random_int(1, 100) <= 35 ? '' : ('Jawaban acak: ' . fake()->sentence(8));
        }

        $options = $question->options->values();
        if ($options->isEmpty()) {
            return null;
        }

        if ($mode === 'perfect') {
            $correct = $options->firstWhere('is_correct', true) ?? $options->first();
            return $correct?->id;
        }

        return $options->random()->id;
    }

    private function resetAttemptData(ExamAttempt $attempt, array $questionIds): void
    {
        $attempt->update([
            'started_at' => now()->subMinutes(random_int(5, 60)),
            'submitted_at' => null,
            'last_seen_at' => now()->subMinutes(random_int(1, 8)),
            'status' => ExamAttemptStatus::InProgress,
            'tab_switches' => 0,
            'total_score' => null,
            'percentage' => null,
            'passed' => null,
        ]);

        StudentAnswer::query()
            ->where('exam_attempt_id', $attempt->id)
            ->whereIn('question_id', $questionIds)
            ->delete();

        ExamActivity::query()
            ->where('exam_id', $attempt->exam_id)
            ->where('exam_attempt_id', $attempt->id)
            ->whereIn('type', ['tab_switch', 'pindah_tab', 'keluar_fullscreen', 'exit_fullscreen', 'fullscreen_exit'])
            ->delete();
    }

    private function resolveActiveExams(Carbon $now)
    {
        $today = $now->toDateString();
        $time = $now->format('H:i:s');

        $base = Exam::query()
            ->with('classrooms:id,name')
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->orderByDesc('id');

        $byStatus = (clone $base)
            ->whereIn('status', ['active', 'running'])
            ->get();

        if ($byStatus->isNotEmpty()) {
            return $byStatus;
        }

        $byWindow = (clone $base)
            ->whereIn('status', ['scheduled', 'active'])
            ->whereDate('date', $today)
            ->whereTime('start_time', '<=', $time)
            ->whereTime('end_time', '>=', $time)
            ->get();

        if ($byWindow->isNotEmpty()) {
            return $byWindow;
        }

        return (clone $base)
            ->whereDate('date', $today)
            ->get();
    }

    private function buildDistribution(int $total): array
    {
        $a = (int) floor($total * 0.60);
        $b = (int) floor($total * 0.20);
        $c = (int) floor($total * 0.10);
        $d = max(0, $total - $a - $b - $c);

        return ['A' => $a, 'B' => $b, 'C' => $c, 'D' => $d];
    }

    /**
     * @param  array<int>  $studentIds
     * @param  array{A:int,B:int,C:int,D:int}  $distribution
     * @return array<int,string>
     */
    private function assignScenarios(array $studentIds, array $distribution): array
    {
        shuffle($studentIds);

        $result = [];
        $offset = 0;

        foreach (['A', 'B', 'C', 'D'] as $scenario) {
            $count = $distribution[$scenario] ?? 0;
            $slice = array_slice($studentIds, $offset, $count);
            foreach ($slice as $studentId) {
                $result[$studentId] = $scenario;
            }
            $offset += $count;
        }

        foreach ($studentIds as $studentId) {
            $result[$studentId] = $result[$studentId] ?? 'A';
        }

        return $result;
    }
}
