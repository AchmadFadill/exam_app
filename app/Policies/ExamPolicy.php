<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    public function view(User $user, Exam $exam): bool
    {
        return $this->canAccessExam($user, $exam);
    }

    public function update(User $user, Exam $exam): bool
    {
        return $this->canAccessExam($user, $exam);
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $this->canAccessExam($user, $exam);
    }

    public function grade(User $user, Exam $exam): bool
    {
        return $this->canAccessExam($user, $exam);
    }

    public function viewReport(User $user, Exam $exam): bool
    {
        return $this->canAccessExam($user, $exam);
    }

    private function canAccessExam(User $user, Exam $exam): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isTeacher() && (int) $exam->teacher_id === (int) $user->teacher?->id;
    }
}
