<?php

namespace App\Livewire\Common\Report;

use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\StudentAnswer;
use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Illuminate\Support\Facades\Gate;

class Detail extends Component
{
    use HasDynamicLayout;

    public $examId;
    public $sortBy = 'default'; // default, highest, lowest, fastest, slowest

    public function mount($id)
    {
        $this->examId = $id;
    }

    public function sortByHighest()
    {
        $this->sortBy = 'highest';
    }

    public function sortByLowest()
    {
        $this->sortBy = 'lowest';
    }

    public function sortByFastest()
    {
        $this->sortBy = 'fastest';
    }

    public function sortBySlowest()
    {
        $this->sortBy = 'slowest';
    }

    public function resetFilter()
    {
        $this->sortBy = 'default';
    }

    private function calculateDuration($start, $end)
    {
        if (!$start || !$end) return 0;
        return $end->diffInMinutes($start);
    }

    public function render()
    {
        $isAdmin = request()->is('admin/*');

        $examModel = \App\Models\Exam::with(['subject', 'classrooms'])->findOrFail($this->examId);
        Gate::authorize('viewReport', $examModel);

        $submittedAttempts = ExamAttempt::query()
            ->where('exam_id', $this->examId)
            ->whereNotNull('submitted_at')
            ->get(['student_id', 'total_score', 'started_at', 'submitted_at'])
            ->keyBy('student_id');

        $submittedAttemptsCollection = $submittedAttempts->values();

        // Exam Summary
        $exam = [
            'id' => $examModel->id,
            'exam_name' => $examModel->name,
            'class' => $examModel->classrooms->pluck('name')->join(', '),
            'subject' => $examModel->subject->name ?? '-',
            'date' => $examModel->date ? $examModel->date->format('d M Y') : '-',
            'avg_score' => $submittedAttemptsCollection->count() > 0 ? number_format($submittedAttemptsCollection->avg('total_score'), 1) : 0,
            'highest' => $submittedAttemptsCollection->max('total_score') ?? 0,
            'lowest' => $submittedAttemptsCollection->min('total_score') ?? 0,
            'participants' => $submittedAttemptsCollection->count()
        ];

        // Fetch All Students in Exam Classrooms
        $classIds = $examModel->classrooms->pluck('id');
        $allStudents = Student::query()
            ->with('user:id,name')
            ->whereIn('classroom_id', $classIds)
            ->get(['id', 'user_id', 'classroom_id']);

        // Merge with Attempts
        $students = $allStudents->map(function($student) use ($submittedAttempts, $examModel) {
            $attempt = $submittedAttempts->get($student->id);
            
            return [
                'id' => $student->id,
                'name' => $student->name,
                'score' => $attempt ? $attempt->total_score : '-',
                'status' => $attempt 
                    ? ($attempt->total_score >= $examModel->passing_grade ? 'Lulus' : 'Tidak Lulus') 
                    : 'Belum Mengerjakan',
                'started_at' => $attempt && $attempt->started_at ? $attempt->started_at->format('H:i') : '-',
                'submitted_at' => $attempt && $attempt->submitted_at ? $attempt->submitted_at->format('H:i') : '-',
                'duration_minutes' => $attempt ? $this->calculateDuration($attempt->started_at, $attempt->submitted_at) : 999999,
                'has_attempt' => (bool) $attempt
            ];
        })->toArray(); // Convert to array for sorting

        // Apply sorting
        if ($this->sortBy === 'highest') {
            usort($students, fn($a, $b) => ($b['score'] === '-' ? -1 : $b['score']) <=> ($a['score'] === '-' ? -1 : $a['score']));
        } elseif ($this->sortBy === 'lowest') {
            usort($students, fn($a, $b) => ($a['score'] === '-' ? 999 : $a['score']) <=> ($b['score'] === '-' ? 999 : $b['score']));
        } elseif ($this->sortBy === 'fastest') {
            usort($students, function($a, $b) {
                // If has_attempt is false, push to bottom
                if (!$a['has_attempt']) return 1; 
                if (!$b['has_attempt']) return -1;
                return $a['duration_minutes'] <=> $b['duration_minutes'];
            });
        } elseif ($this->sortBy === 'slowest') {
             usort($students, function($a, $b) {
                if (!$a['has_attempt']) return 1; 
                if (!$b['has_attempt']) return -1;
                return $b['duration_minutes'] <=> $a['duration_minutes'];
            });
        }

        // Most Failed Questions Analysis
        // Logic: Get wrong answers for this exam, group by question_id, count
        $most_failed_questions = StudentAnswer::query()
            ->join('exam_attempts', 'student_answers.exam_attempt_id', '=', 'exam_attempts.id')
            ->join('questions', 'student_answers.question_id', '=', 'questions.id')
            ->where('exam_attempts.exam_id', $this->examId)
            ->whereNotNull('exam_attempts.submitted_at')
            ->where('student_answers.is_correct', false) // Provided grading sets this
            ->selectRaw('questions.id, questions.text, questions.answer_key, count(*) as failed_count')
            ->groupBy('questions.id', 'questions.text', 'questions.answer_key')
            ->orderByDesc('failed_count')
            ->limit(5)
            ->get()
            ->map(function($q) use ($submittedAttemptsCollection) {
                $totalAttempts = $submittedAttemptsCollection->count();
                $percentage = $totalAttempts > 0 ? round(($q->failed_count / $totalAttempts) * 100) : 0;
                
                return [
                    'number' => $q->id, // Or map to sequence if possible, id is okay for now
                    'text' => strip_tags($q->text), // Clean HTML
                    'failed_count' => $q->failed_count,
                    'failed_percentage' => $percentage,
                    'correct_answer' => $q->answer_key
                ];
            });

        return $this->applyLayout('livewire.common.report.detail', [
            'exam' => $exam,
            'students' => $students,
            'most_failed_questions' => $most_failed_questions,
            'backRoute' => $isAdmin ? 'admin.reports.index' : 'teacher.reports.index',
            'studentDetailRoute' => $isAdmin ? 'admin.reports.student' : 'teacher.reports.student',
            'analysisRoute' => $isAdmin ? 'admin.reports.analysis' : 'teacher.reports.analysis'
        ]);
    }

}
