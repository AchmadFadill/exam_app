<?php

namespace App\Livewire\Student;

use App\Enums\ExamAttemptStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

class ExamList extends Component
{
    use WithPagination;

    public $filter = 'all'; // all, active, upcoming, history

    protected $queryString = ['filter'];

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage('studentExamPage');
    }

    public function getExamsProperty()
    {
        $student = \Illuminate\Support\Facades\Auth::user()->student;
        
        if (!$student) {
            return new LengthAwarePaginator(
                items: [],
                total: 0,
                perPage: 10,
                currentPage: 1,
                options: ['path' => request()->url(), 'pageName' => 'studentExamPage']
            );
        }

        $query = \App\Models\Exam::whereHas('classrooms', function($q) use ($student) {
            $q->where('classroom_id', $student->classroom_id);
        })
        ->with('classrooms')
        ->with(['subject', 'teacher.user', 'attempts' => function($q) use ($student) {
            $q->where('student_id', $student->id);
        }]);

        return match($this->filter) {
            'active' => $this->getActiveExams($query, $student),
            'upcoming' => $this->getUpcomingExams($query),
            'history' => $this->getHistoryExams($query, $student),
            default => $query->orderBy('date', 'desc')->orderBy('start_time')->paginate(10, ['*'], 'studentExamPage')
        };
    }

    private function getActiveExams($query, $student)
    {
        // Scheduled for today, within time window, NO submitted attempt
        return $query->where('status', 'scheduled')
            ->whereDate('date', now())
            ->whereTime('start_time', '<=', now()->format('H:i'))
            ->whereTime('end_time', '>=', now()->format('H:i'))
            ->whereDoesntHave('attempts', function($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->whereNotNull('submitted_at');
            })
            ->paginate(10, ['*'], 'studentExamPage');
    }

    private function getUpcomingExams($query)
    {
        // Scheduled for future
        return $query->where('status', 'scheduled')
            ->where(function($q) {
                $q->whereDate('date', '>', now())
                  ->orWhere(function($subq) {
                      $subq->whereDate('date', now())
                           ->whereTime('start_time', '>', now()->format('H:i'));
                  });
            })
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(10, ['*'], 'studentExamPage');
    }

    private function getHistoryExams($query, $student)
    {
        // Completed attempts OR past exams
        return $query->where(function($q) use ($student) {
            $q->whereHas('attempts', function($subq) use ($student) {
                $subq->where('student_id', $student->id)
                     ->whereNotNull('submitted_at');
            })
            ->orWhere('date', '<', now()->subDay()); // Simple check for past exams
        })
        ->orderBy('date', 'desc')
        ->paginate(10, ['*'], 'studentExamPage');
    }

    public function getExamStatus($exam)
    {
        $student = \Illuminate\Support\Facades\Auth::user()->student;
        $attempt = $exam->attempts->where('student_id', $student->id)->first();
        $now = now();
        $start = \Carbon\Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->start_time);
        $end = \Carbon\Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->end_time);

        if ($attempt) {
            if ($attempt->submitted_at || (($attempt->status instanceof ExamAttemptStatus ? $attempt->status : ExamAttemptStatus::tryFrom((string) $attempt->status))?->isFinalized() ?? false)) {
                return ExamAttemptStatus::Submitted->value;
            }

            // Attempt exists but exam window has passed -> should not be resumable.
            if ($now->gt($end)) {
                return 'missed';
            }

            return ExamAttemptStatus::InProgress->value;
        }

        if ($now->between($start, $end)) return 'active';
        if ($now->lt($start)) return 'upcoming';
        return 'missed';
    }

    public function render()
    {
        return view('livewire.student.exam-list', [
            'exams' => $this->exams
        ])->layout('layouts.student', ['title' => 'Daftar Ujian']);
    }
}
