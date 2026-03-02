<?php

namespace App\Console\Commands;

use App\Models\Exam;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GenerateStagingData extends Command
{
    private const STUDENT_COUNT = 1000;
    private const EXAM_COUNT = 5;
    private const QUESTIONS_PER_EXAM = 50;
    private const CHUNK_SIZE = 200;
    private const CLASS_NAME = 'STAGING-CLASS';
    private const SUBJECT_NAME = 'STAGING-SUBJECT';
    private const TEACHER_EMAIL = 'staging.teacher@nexa.local';
    private const TEACHER_NAME = 'STAGING Teacher';
    private const QUESTION_TITLE_PREFIX = 'STAGING EXAM ';

    protected $signature = 'nexa:setup-staging';

    protected $description = 'Prepare a high-concurrency staging dataset with 1,000 students, 5 active exams, and 250 questions';

    public function handle(): int
    {
        $this->info('Preparing staging environment...');

        try {
            DB::transaction(function (): void {
                $this->cleanupExistingStagingData();

                $now = now();
                $hashedPassword = Hash::make('password123');

                $teacher = $this->createOrUpdateStagingTeacher($hashedPassword);
                $subject = $this->createOrUpdateStagingSubject();
                $this->attachTeacherToSubject($teacher->id, $subject->id, $now);
                $classroomId = $this->createOrUpdateStagingClassroom($teacher->id, $now);

                $studentUserIds = $this->createStudentUsers($hashedPassword, $now);
                $this->createStudents($studentUserIds, $classroomId, $now);

                $examIds = $this->createExams($teacher->id, $subject->id, $now);
                $this->attachExamsToClassroom($examIds, $classroomId, $now);
                $this->createQuestionsAndExamLinks($teacher->id, $subject->id, $examIds, $now);
            }, 5);
        } catch (\Throwable $e) {
            $this->error('Staging setup failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Staging environment is ready.');
        $this->line('Student login pattern: student1@nexa.local .. student1000@nexa.local');
        $this->line('Password: password123');

        return self::SUCCESS;
    }

    private function cleanupExistingStagingData(): void
    {
        $teacherUserId = DB::table('users')->where('email', self::TEACHER_EMAIL)->value('id');
        $teacherId = $teacherUserId
            ? DB::table('teachers')->where('user_id', $teacherUserId)->value('id')
            : null;

        $subjectId = DB::table('subjects')->where('name', self::SUBJECT_NAME)->value('id');
        $classroomId = DB::table('classrooms')->where('name', self::CLASS_NAME)->value('id');

        $examIds = DB::table('exams')
            ->when($teacherId, fn ($query) => $query->where('teacher_id', $teacherId))
            ->when($subjectId, fn ($query) => $query->orWhere('subject_id', $subjectId))
            ->where(function ($query) use ($teacherId, $subjectId) {
                if ($teacherId) {
                    $query->where('teacher_id', $teacherId);
                }
                if ($subjectId) {
                    $query->orWhere('subject_id', $subjectId);
                }
            })
            ->pluck('id')
            ->all();

        if (!empty($examIds)) {
            DB::table('exam_answers')->whereIn('exam_session_id', function ($query) use ($examIds) {
                $query->select('id')->from('exam_sessions')->whereIn('exam_id', $examIds);
            })->delete();
            DB::table('student_answers')->whereIn('exam_attempt_id', function ($query) use ($examIds) {
                $query->select('id')->from('exam_attempts')->whereIn('exam_id', $examIds);
            })->delete();
            DB::table('exam_sessions')->whereIn('exam_id', $examIds)->delete();
            DB::table('exam_attempts')->whereIn('exam_id', $examIds)->delete();
            DB::table('exam_classes')->whereIn('exam_id', $examIds)->delete();
            DB::table('exam_questions')->whereIn('exam_id', $examIds)->delete();
            DB::table('exams')->whereIn('id', $examIds)->delete();
        }

        if ($teacherId || $subjectId) {
            $questionIds = DB::table('questions')
                ->when($teacherId, fn ($query) => $query->where('teacher_id', $teacherId))
                ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
                ->pluck('id')
                ->all();

            if (!empty($questionIds)) {
                DB::table('exam_questions')->whereIn('question_id', $questionIds)->delete();
                DB::table('student_answers')->whereIn('question_id', $questionIds)->delete();
                DB::table('exam_answers')->whereIn('question_id', $questionIds)->delete();
                DB::table('question_options')->whereIn('question_id', $questionIds)->delete();
                DB::table('questions')->whereIn('id', $questionIds)->delete();
            }
        }

        if ($classroomId) {
            $studentIds = DB::table('students')->where('classroom_id', $classroomId)->pluck('id')->all();
            $userIds = DB::table('students')->where('classroom_id', $classroomId)->pluck('user_id')->all();

            if (!empty($studentIds)) {
                DB::table('student_answers')->whereIn('exam_attempt_id', function ($query) use ($studentIds) {
                    $query->select('id')->from('exam_attempts')->whereIn('student_id', $studentIds);
                })->delete();
                DB::table('exam_answers')->whereIn('exam_session_id', function ($query) use ($studentIds) {
                    $query->select('id')->from('exam_sessions')->whereIn('student_id', $studentIds);
                })->delete();
                DB::table('exam_attempts')->whereIn('student_id', $studentIds)->delete();
                DB::table('exam_sessions')->whereIn('student_id', $studentIds)->delete();
                DB::table('students')->whereIn('id', $studentIds)->delete();
            }

            if (!empty($userIds)) {
                DB::table('sessions')->whereIn('user_id', $userIds)->delete();
                DB::table('users')->whereIn('id', $userIds)->delete();
            }

            DB::table('classrooms')->where('id', $classroomId)->delete();
        }

        if ($teacherId) {
            DB::table('subject_teacher')->where('teacher_id', $teacherId)->delete();
            DB::table('teachers')->where('id', $teacherId)->delete();
        }

        if ($teacherUserId) {
            DB::table('users')->where('id', $teacherUserId)->delete();
        }

        if ($subjectId) {
            DB::table('subjects')->where('id', $subjectId)->delete();
        }
    }

    private function createOrUpdateStagingTeacher(string $hashedPassword): Teacher
    {
        $now = now();

        $user = User::query()->updateOrCreate(
            ['email' => self::TEACHER_EMAIL],
            [
                'name' => self::TEACHER_NAME,
                'role' => 'teacher',
                'password' => $hashedPassword,
                'must_change_password' => false,
                'email_verified_at' => $now,
            ]
        );

        return Teacher::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['nip' => 'STAGING-TEACHER']
        );
    }

    private function createOrUpdateStagingSubject(): Subject
    {
        return Subject::query()->updateOrCreate(
            ['name' => self::SUBJECT_NAME],
            ['code' => 'STG-SUBJ']
        );
    }

    private function attachTeacherToSubject(int $teacherId, int $subjectId, $now): void
    {
        DB::table('subject_teacher')->updateOrInsert(
            ['teacher_id' => $teacherId, 'subject_id' => $subjectId],
            ['created_at' => $now, 'updated_at' => $now]
        );
    }

    private function createOrUpdateStagingClassroom(int $teacherId, $now): int
    {
        $existingId = DB::table('classrooms')->where('name', self::CLASS_NAME)->value('id');

        if ($existingId) {
            DB::table('classrooms')
                ->where('id', $existingId)
                ->update([
                    'level' => 'X',
                    'teacher_id' => $teacherId,
                    'updated_at' => $now,
                ]);

            return (int) $existingId;
        }

        return (int) DB::table('classrooms')->insertGetId([
            'name' => self::CLASS_NAME,
            'level' => 'X',
            'teacher_id' => $teacherId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function createStudentUsers(string $hashedPassword, $now): array
    {
        $users = [];

        for ($i = 1; $i <= self::STUDENT_COUNT; $i++) {
            $users[] = [
                'name' => 'student' . $i,
                'email' => 'student' . $i . '@nexa.local',
                'email_verified_at' => $now,
                'password' => $hashedPassword,
                'role' => 'student',
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($users, self::CHUNK_SIZE) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        return DB::table('users')
            ->where('email', 'like', 'student%@nexa.local')
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }

    private function createStudents(array $studentUserIds, int $classroomId, $now): void
    {
        $students = [];

        foreach (array_values($studentUserIds) as $index => $userId) {
            $number = $index + 1;
            $students[] = [
                'user_id' => $userId,
                'nis' => 'STG' . str_pad((string) $number, 6, '0', STR_PAD_LEFT),
                'classroom_id' => $classroomId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($students, self::CHUNK_SIZE) as $chunk) {
            DB::table('students')->insert($chunk);
        }
    }

    private function createExams(int $teacherId, int $subjectId, $now): array
    {
        $examIds = [];
        $date = $now->toDateString();
        $startTime = $now->copy()->subHour()->format('H:i:s');
        $endTime = $now->copy()->addHours(5)->format('H:i:s');

        for ($i = 1; $i <= self::EXAM_COUNT; $i++) {
            $examIds[] = (int) DB::table('exams')->insertGetId([
                'teacher_id' => $teacherId,
                'subject_id' => $subjectId,
                'name' => 'STAGING EXAM ' . $i,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => 300,
                'token' => Exam::generateToken(),
                'passing_grade' => 70,
                'default_score' => 2,
                'shuffle_questions' => false,
                'shuffle_answers' => false,
                'enable_tab_tolerance' => false,
                'tab_tolerance' => 3,
                'show_score_to_student' => true,
                'show_answers_to_student' => true,
                'is_published' => true,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $examIds;
    }

    private function attachExamsToClassroom(array $examIds, int $classroomId, $now): void
    {
        $rows = array_map(fn (int $examId) => [
            'exam_id' => $examId,
            'classroom_id' => $classroomId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $examIds);

        foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
            DB::table('exam_classes')->insert($chunk);
        }
    }

    private function createQuestionsAndExamLinks(int $teacherId, int $subjectId, array $examIds, $now): void
    {
        foreach ($examIds as $examIndex => $examId) {
            $title = self::QUESTION_TITLE_PREFIX . ($examIndex + 1);
            $questionRows = [];

            for ($i = 1; $i <= self::QUESTIONS_PER_EXAM; $i++) {
                $questionRows[] = [
                    'teacher_id' => $teacherId,
                    'subject_id' => $subjectId,
                    'title' => $title,
                    'type' => 'multiple_choice',
                    'text' => $this->makeQuestionText($examIndex + 1, $i),
                    'image_path' => null,
                    'explanation' => 'STAGING explanation for question ' . $i,
                    'score' => 2,
                    'answer_key' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('questions')->insert($questionRows);

            $questionIds = DB::table('questions')
                ->where('teacher_id', $teacherId)
                ->where('subject_id', $subjectId)
                ->where('title', $title)
                ->orderBy('id')
                ->pluck('id')
                ->all();

            $optionRows = [];
            $pivotRows = [];

            foreach (array_values($questionIds) as $index => $questionId) {
                $correctLabel = ['A', 'B', 'C', 'D', 'E'][array_rand(['A', 'B', 'C', 'D', 'E'])];

                foreach (['A', 'B', 'C', 'D', 'E'] as $label) {
                    $optionRows[] = [
                        'question_id' => $questionId,
                        'label' => $label,
                        'text' => 'Option ' . $label . ' for Q' . ($index + 1) . ' exam ' . ($examIndex + 1),
                        'image_path' => null,
                        'is_correct' => $label === $correctLabel,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $pivotRows[] = [
                    'exam_id' => $examId,
                    'question_id' => $questionId,
                    'order' => $index + 1,
                    'score' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($optionRows, self::CHUNK_SIZE) as $chunk) {
                DB::table('question_options')->insert($chunk);
            }

            foreach (array_chunk($pivotRows, self::CHUNK_SIZE) as $chunk) {
                DB::table('exam_questions')->insert($chunk);
            }
        }
    }

    private function makeQuestionText(int $examNumber, int $questionNumber): string
    {
        return sprintf(
            'STAGING Exam %d Question %d :: %s',
            $examNumber,
            $questionNumber,
            Str::upper(Str::random(24))
        );
    }
}
