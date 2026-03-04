<?php

namespace App\Livewire\Common\Report;

use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class QuestionAnalysis extends Component
{
    use HasDynamicLayout;

    public $exam;
    public $questions;

    public function mount($examId)
    {
        $this->exam = \App\Models\Exam::findOrFail($examId);
        Gate::authorize('viewReport', $this->exam);
        $finalizedStatuses = collect(\App\Enums\ExamAttemptStatus::finalized())
            ->map(fn (\App\Enums\ExamAttemptStatus $status) => $status->value)
            ->all();

        $questions = $this->exam->questions()
            ->with(['options'])
            ->get();

        $questionIds = $questions->pluck('id');

        $statsByQuestion = DB::table('student_answers')
            ->join('exam_attempts', 'student_answers.exam_attempt_id', '=', 'exam_attempts.id')
            ->where('exam_attempts.exam_id', $examId)
            ->whereIn('student_answers.question_id', $questionIds)
            ->where(function ($q) use ($finalizedStatuses) {
                $q->whereNotNull('exam_attempts.submitted_at')
                    ->orWhereIn('exam_attempts.status', $finalizedStatuses);
            })
            ->groupBy('student_answers.question_id')
            ->select(
                'student_answers.question_id',
                DB::raw('count(*) as total_answers'),
                DB::raw('sum(case when student_answers.is_correct = 1 then 1 else 0 end) as correct_count'),
                DB::raw('sum(case when student_answers.is_correct = 0 then 1 else 0 end) as wrong_count')
            )
            ->get()
            ->keyBy('question_id');

        $distributionByQuestion = DB::table('student_answers')
            ->join('exam_attempts', 'student_answers.exam_attempt_id', '=', 'exam_attempts.id')
            ->where('exam_attempts.exam_id', $examId)
            ->whereIn('student_answers.question_id', $questionIds)
            ->where(function ($q) use ($finalizedStatuses) {
                $q->whereNotNull('exam_attempts.submitted_at')
                    ->orWhereIn('exam_attempts.status', $finalizedStatuses);
            })
            ->whereNotNull('student_answers.selected_option_id')
            ->groupBy('student_answers.question_id', 'student_answers.selected_option_id')
            ->select(
                'student_answers.question_id',
                'student_answers.selected_option_id',
                DB::raw('count(*) as count')
            )
            ->get()
            ->groupBy('question_id')
            ->map(function ($rows) {
                return $rows->pluck('count', 'selected_option_id')->toArray();
            });

        $this->questions = $questions->map(function ($question) use ($statsByQuestion, $distributionByQuestion) {
            $question->stats = $statsByQuestion->get($question->id) ?? (object) [
                'total_answers' => 0,
                'correct_count' => 0,
                'wrong_count' => 0,
            ];

            $question->distribution = $distributionByQuestion->get($question->id, []);

            return $question;
        });
    }

    public function render()
    {
        $isAdmin = request()->is('admin/*');

        return $this->applyLayout('livewire.common.report.question-analysis', [
            'backRoute' => $isAdmin ? 'admin.reports.detail' : 'teacher.reports.detail',
            'backParam' => $this->exam->id
        ]);
    }

}
