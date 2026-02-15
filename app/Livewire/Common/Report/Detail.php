<?php

namespace App\Livewire\Common\Report;

use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\StudentAnswer;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Enums\ExamAttemptStatus;
use Illuminate\Pagination\LengthAwarePaginator;

class Detail extends Component
{
    use HasDynamicLayout, WithPagination;

    public $examId;
    public $sortBy = 'default'; // default, highest, lowest, fastest, slowest
    public $classroomFilter = '';

    public function mount($id)
    {
        $this->examId = $id;
    }

    public function sortByHighest()
    {
        $this->sortBy = 'highest';
        $this->resetPage('studentsPage');
    }

    public function sortByLowest()
    {
        $this->sortBy = 'lowest';
        $this->resetPage('studentsPage');
    }

    public function sortByFastest()
    {
        $this->sortBy = 'fastest';
        $this->resetPage('studentsPage');
    }

    public function sortBySlowest()
    {
        $this->sortBy = 'slowest';
        $this->resetPage('studentsPage');
    }

    public function resetFilter()
    {
        $this->sortBy = 'default';
        $this->resetPage('studentsPage');
    }

    public function updatedClassroomFilter()
    {
        $this->resetPage('studentsPage');
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
            // Removed whereNotNull('submitted_at') to include InProgress, Abandoned, etc.
            ->withCount('answers')
            ->withSum('answers', 'score_awarded')
            ->get(['student_id', 'total_score', 'started_at', 'submitted_at', 'status'])
            ->keyBy('student_id');

        $submittedAttemptsCollection = $submittedAttempts->values();

        $essayQuestionIds = $examModel->questions()
            ->where('type', 'essay')
            ->pluck('questions.id');
        $essayQuestionCount = $essayQuestionIds->count();
        $examEndAt = \Carbon\Carbon::parse($examModel->date->format('Y-m-d') . ' ' . $examModel->end_time);
        $examWindowEnded = now()->gt($examEndAt);
        $questionNumberMap = $examModel->questions()
            ->pluck('questions.id')
            ->values()
            ->flip()
            ->map(fn ($index) => $index + 1);

        // Exam Summary (Calculations should filter for valid/finished attempts if needed, 
        // but for now simple stats on all attempts is acceptable or we can filter strictly for stats)
        // Let's keep stats based on submitted/graded attempts to avoid skewing with InProgress 0s
        $finishedAttempts = $submittedAttemptsCollection->filter(fn($a) => $a->status !== ExamAttemptStatus::InProgress);
        
        $exam = [
            'id' => $examModel->id,
            'exam_name' => $examModel->name,
            'class' => $examModel->classrooms->pluck('name')->join(', '),
            'subject' => $examModel->subject->name ?? '-',
            'date' => $examModel->date ? $examModel->date->format('d M Y') : '-',
            'avg_score' => $finishedAttempts->count() > 0 ? number_format($finishedAttempts->avg('total_score'), 1) : 0,
            'highest' => $finishedAttempts->max('total_score') ?? 0,
            'lowest' => $finishedAttempts->min('total_score') ?? 0,
            'participants' => $submittedAttemptsCollection->count() // Count all who started
        ];

        // Fetch All Students in Exam Classrooms (with optional class filter)
        $classIds = $examModel->classrooms->pluck('id');
        $allStudentsQuery = Student::query()
            ->with('user:id,name')
            ->whereIn('classroom_id', $classIds)
            ->when(
                filled($this->classroomFilter),
                fn ($q) => $q->where('classroom_id', (int) $this->classroomFilter)
            );
        $allStudents = $allStudentsQuery->get(['id', 'user_id', 'classroom_id']);

        // Merge with Attempts
        $students = $allStudents->map(function($student) use ($submittedAttempts, $examModel, $essayQuestionCount, $examWindowEnded) {
            $attempt = $submittedAttempts->get($student->id);

            if (!$attempt) {
                $status = 'Belum Mengerjakan';
            } elseif ($attempt->status === ExamAttemptStatus::Graded) {
                $status = $attempt->total_score >= $examModel->passing_grade ? 'Lulus' : 'Tidak Lulus';
            } elseif (in_array($attempt->status, [ExamAttemptStatus::Submitted, ExamAttemptStatus::Completed, ExamAttemptStatus::TimedOut, ExamAttemptStatus::Abandoned])) {
                // Check if needs grading (has essays)
                if ($essayQuestionCount > 0) {
                    $status = 'Pending Penilaian';
                } else {
                    $status = $attempt->total_score >= $examModel->passing_grade ? 'Lulus' : 'Tidak Lulus';
                }
            } elseif ($attempt->status === ExamAttemptStatus::InProgress || $attempt->status === ExamAttemptStatus::Ongoing) {
                $hasNoAnswer = (int) ($attempt->answers_count ?? 0) === 0;
                if ($hasNoAnswer) {
                    $status = $examWindowEnded ? 'Tidak Mengerjakan' : 'Belum Mengerjakan';
                } else {
                    $status = $examWindowEnded ? 'Waktu Habis' : 'Sedang Mengerjakan';
                }
            } else {
                // Fallback
                $status = $attempt->total_score >= $examModel->passing_grade ? 'Lulus' : 'Tidak Lulus';
            }

            $resolvedScore = '-';
            if ($attempt) {
                if (!is_null($attempt->total_score)) {
                    $resolvedScore = $attempt->total_score;
                } elseif ((int) ($attempt->answers_count ?? 0) > 0) {
                    // Fallback for unfinished/timeout attempts: use accumulated awarded scores.
                    $resolvedScore = (float) ($attempt->answers_sum_score_awarded ?? 0);
                }
            }
            
            return [
                'id' => $student->id,
                'name' => $student->name,
                'score' => $resolvedScore,
                'status' => $status,
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

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage('studentsPage');
        $studentsCollection = collect($students);
        $studentsPage = $studentsCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $students = new LengthAwarePaginator(
            $studentsPage,
            $studentsCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'studentsPage',
            ]
        );

        // Most Failed Questions Analysis
        // Logic: Get wrong answers for this exam, group by question_id, count
        $most_failed_questions = StudentAnswer::query()
            ->join('exam_attempts', 'student_answers.exam_attempt_id', '=', 'exam_attempts.id')
            ->join('questions', 'student_answers.question_id', '=', 'questions.id')
            ->where('exam_attempts.exam_id', $this->examId)
            ->whereNotNull('exam_attempts.submitted_at')
            ->where('student_answers.is_correct', false) // Provided grading sets this
            ->selectRaw('questions.id as question_id, questions.text, questions.answer_key, count(*) as failed_count')
            ->groupBy('questions.id', 'questions.text', 'questions.answer_key')
            ->orderByDesc('failed_count')
            ->limit(5)
            ->get()
            ->map(function($q) use ($submittedAttemptsCollection, $questionNumberMap) {
                $totalAttempts = $submittedAttemptsCollection->count();
                $percentage = $totalAttempts > 0 ? round(($q->failed_count / $totalAttempts) * 100) : 0;
                
                return [
                    'number' => $questionNumberMap->get((int) $q->question_id, '-'),
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
            'assigned_classes' => $examModel->classrooms->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]),
            'backRoute' => $isAdmin ? 'admin.reports.index' : 'teacher.reports.index',
            'printRoute' => $isAdmin ? 'admin.reports.print' : 'teacher.reports.print',
            'studentDetailRoute' => $isAdmin ? 'admin.reports.student' : 'teacher.reports.student',
            'analysisRoute' => $isAdmin ? 'admin.reports.analysis' : 'teacher.reports.analysis'
        ]);
    }

}
