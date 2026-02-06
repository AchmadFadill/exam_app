<?php

namespace App\Livewire\Common\Report;

use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Illuminate\Support\Facades\DB;

class QuestionAnalysis extends Component
{
    use HasDynamicLayout;

    public $exam;
    public $questions;

    public function mount($examId)
    {
        $this->exam = \App\Models\Exam::findOrFail($examId);
        
        // Fetch all questions for this exam
        // Calculate statistics for each
        // Fetch all questions for this exam
        // Calculate statistics for each
        $this->questions = $this->exam->questions()
            ->with(['options'])
            ->get()
            ->map(function($q) use ($examId) {
                // Get all answers for this question in this exam
                $stats = DB::table('student_answers')
                    ->join('exam_attempts', 'student_answers.exam_attempt_id', '=', 'exam_attempts.id')
                    ->where('exam_attempts.exam_id', $examId)
                    ->where('student_answers.question_id', $q->id)
                    ->whereNotNull('exam_attempts.submitted_at')
                    ->select(
                        DB::raw('count(*) as total_answers'),
                        DB::raw('sum(case when student_answers.is_correct = 1 then 1 else 0 end) as correct_count'),
                        DB::raw('sum(case when student_answers.is_correct = 0 then 1 else 0 end) as wrong_count')
                    )
                    ->first();

                // Option Distribution
                $distribution = DB::table('student_answers')
                    ->join('exam_attempts', 'student_answers.exam_attempt_id', '=', 'exam_attempts.id')
                    ->where('exam_attempts.exam_id', $examId)
                    ->where('student_answers.question_id', $q->id)
                    ->whereNotNull('exam_attempts.submitted_at')
                    ->select('selected_option_id', DB::raw('count(*) as count'))
                    ->groupBy('selected_option_id')
                    ->pluck('count', 'selected_option_id')
                    ->toArray();

                $q->stats = $stats;
                $q->distribution = $distribution;
                
                return $q;
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
