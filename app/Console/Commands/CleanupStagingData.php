<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupStagingData extends Command
{
    private const CLASS_NAME = 'STAGING-CLASS';
    private const SUBJECT_NAME = 'STAGING-SUBJECT';
    private const TEACHER_EMAIL = 'staging.teacher@nexa.local';

    protected $signature = 'nexa:cleanup-staging';

    protected $description = 'Cleanup staging dataset: truncate sessions and remove STAGING-CLASS related data';

    public function handle(): int
    {
        $this->info('Cleaning staging data...');

        try {
            DB::transaction(function (): void {
                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')->truncate();
                }

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
            }, 5);
        } catch (\Throwable $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Staging cleanup complete.');
        return self::SUCCESS;
    }
}
