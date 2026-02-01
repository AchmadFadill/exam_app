<?php

namespace App\Livewire\Student;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $student = \Illuminate\Support\Facades\Auth::user()->student;
        
        if (!$student) {
            // Fallback for non-student users previewing the dashboard
            return view('student.dashboard', [
                'greeting' => $this->getGreeting(),
                'active_exams' => [],
                'upcoming_exams' => [],
                'stats' => ['avg_score' => 0, 'attendance' => 0, 'completed_exams' => 0]
            ])->layout('layouts.student', ['title' => 'Dashboard Siswa']);
        }

        // Real active exams (happening now)
        // Check exams scheduled for today, within time window, assigned to student's class
        $active_exams = \App\Models\Exam::where('status', 'scheduled')
            ->whereDate('date', now())
            ->whereTime('start_time', '<=', now()->format('H:i'))
            ->whereTime('end_time', '>=', now()->format('H:i'))
            ->whereHas('classrooms', function($q) use ($student) {
                $q->where('classroom_id', $student->classroom_id);
            })
            // Filter out exams already submitted by this student
            ->whereDoesntHave('attempts', function($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->whereNotNull('submitted_at');
            })
            ->with(['subject', 'teacher.user'])
            ->get()
            ->map(function($exam) {
                return [
                    'id' => $exam->id,
                    'subject' => $exam->subject->name,
                    'title' => $exam->name,
                    'teacher' => $exam->teacher->user->name,
                    'start_time' => \Carbon\Carbon::parse($exam->start_time)->format('H:i'),
                    'end_time' => \Carbon\Carbon::parse($exam->end_time)->format('H:i'),
                    'duration' => $exam->duration_minutes,
                    'questions_count' => $exam->questions()->count(),
                    'status' => 'ongoing',
                    'is_urgent' => \Carbon\Carbon::parse($exam->end_time)->diffInMinutes(now()) < 30,
                ];
            });

        // Upcoming exams (next 7 days)
        $upcoming_exams = \App\Models\Exam::where('status', 'scheduled')
            ->where(function($query) {
                $query->whereDate('date', '>', now())
                      ->orWhere(function($q) {
                          $q->whereDate('date', now())
                            ->whereTime('start_time', '>', now()->format('H:i'));
                      });
            })
            ->whereDate('date', '<=', now()->addDays(7))
            ->whereHas('classrooms', function($q) use ($student) {
                $q->where('classroom_id', $student->classroom_id);
            })
            ->with(['subject'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(function($exam) {
                return [
                    'subject' => $exam->subject->name,
                    'title' => $exam->name,
                    'date' => \Carbon\Carbon::parse($exam->date)->translatedFormat('l, d M') . ' • ' . \Carbon\Carbon::parse($exam->start_time)->format('H:i'),
                    'class' => $exam->classrooms->pluck('name')->join(', ') // Display assigned classes just for info
                ];
            });

        // Calculate REAL stats
        $attempts = \App\Models\ExamAttempt::where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->get();
            
        $stats = [
            'avg_score' => $attempts->avg('total_score') ?? 0,
            'attendance' => $attempts->count(), // Simply count completed exams for now
            'completed_exams' => $attempts->count()
        ];

        return view('student.dashboard', [
            'greeting' => $this->getGreeting(),
            'active_exams' => $active_exams,
            'upcoming_exams' => $upcoming_exams,
            'stats' => $stats
        ])->layout('layouts.student', ['title' => 'Dashboard Siswa']);
    }

    private function getGreeting()
    {
        $hour = date('H');
        if ($hour < 11) return 'Selamat Pagi';
        if ($hour < 15) return 'Selamat Siang';
        if ($hour < 19) return 'Selamat Sore';
        return 'Selamat Malam';
    }
}
