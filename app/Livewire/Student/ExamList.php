<?php

namespace App\Livewire\Student;

use Livewire\Component;

class ExamList extends Component
{
    public $filter = 'all'; // all, active, upcoming, history

    protected $queryString = ['filter'];

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    public function getExamsProperty()
    {
        $student = \Illuminate\Support\Facades\Auth::user()->student;
        
        if (!$student) return collect([]);

        $query = \App\Models\Exam::whereHas('classrooms', function($q) use ($student) {
            $q->where('classroom_id', $student->classroom_id);
        })
        ->with(['subject', 'teacher.user', 'attempts' => function($q) use ($student) {
            $q->where('student_id', $student->id);
        }]);

        return match($this->filter) {
            'active' => $this->getActiveExams($query, $student),
            'upcoming' => $this->getUpcomingExams($query),
            'history' => $this->getHistoryExams($query, $student),
            default => $query->orderBy('date', 'desc')->orderBy('start_time')->get()
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
            ->get();
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
            ->get();
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
        ->get();
    }

    public function getExamStatus($exam)
    {
        $student = \Illuminate\Support\Facades\Auth::user()->student;
        $attempt = $exam->attempts->where('student_id', $student->id)->first();

        if ($attempt) {
            if ($attempt->submitted_at) return 'submitted';
            return 'in_progress';
        }

        $now = now();
        $start = \Carbon\Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->start_time);
        $end = \Carbon\Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->end_time);

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
