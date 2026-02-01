<?php

namespace App\Livewire\Teacher;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $teacher = $user->teacher;
        
        if (!$teacher) {
            return view('teacher.dashboard', [
                'stats' => [
                    'active_exams' => 0,
                    'grading_needed' => 0,
                    'completed_exams' => 0,
                    'questions_count' => 0
                ],
                'ongoing_exams' => [],
                'upcoming_exams' => [],
                'recent_activities' => [],
                'greeting' => $this->getGreeting()
            ])->layout('layouts.teacher', ['title' => 'Dashboard Guru']);
        }

        // 1. Stats
        $activeExamsCount = \App\Models\Exam::where('teacher_id', $teacher->id)
            ->where('status', 'scheduled')
            ->whereDate('date', now())
            ->whereTime('start_time', '<=', now()->format('H:i'))
            ->whereTime('end_time', '>=', now()->format('H:i'))
            ->count();
            
        // 3. Average Score (unused in view but good to calculate)
        // $avgScore = ...

        // 4. Pending Grading (status = submitted)
        $gradingNeeded = \App\Models\ExamAttempt::whereHas('exam', function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->where('status', 'submitted')->count();

        // 5. Completed Exams (status = finished OR time passed)
        $completedExams = \App\Models\Exam::where('teacher_id', $teacher->id)
            ->where(function($q) {
                $q->where('status', 'finished')
                  ->orWhere(function($sq) {
                      $sq->where('status', 'scheduled')
                         ->where('end_time', '<', now()->format('H:i'))
                         ->whereDate('date', '<=', now());
                  });
            })->count();

        // 6. Questions Count
        $questionsCount = \App\Models\Question::where('teacher_id', $teacher->id)->count();

        $stats = [
            'active_exams' => $activeExamsCount,
            'grading_needed' => $gradingNeeded,
            'completed_exams' => $completedExams,
            'questions_count' => $questionsCount
        ];

        // 2. Ongoing Exams
        $ongoing_exams = \App\Models\Exam::where('teacher_id', $teacher->id)
            ->where('status', 'scheduled')
            ->whereDate('date', now())
            ->whereTime('start_time', '<=', now()->format('H:i'))
            ->whereTime('end_time', '>=', now()->format('H:i'))
            ->withCount(['attempts' => function($q) {
                $q->where('status', 'in_progress');
            }])
            ->get()
            ->map(function($exam) {
                $totalStudents = $exam->classrooms->sum(function($c) { return $c->students()->count(); });
                $finishedCount = $exam->attempts()->whereIn('status', ['submitted', 'graded'])->count();
                $percentage = $totalStudents > 0 ? round(($finishedCount / $totalStudents) * 100) : 0;

                return [
                    'id' => $exam->id,
                    'subject' => $exam->subject->name,
                    'class' => $exam->classrooms->pluck('name')->join(', '),
                    'name' => $exam->name,
                    'participants' => $exam->attempts_count, // Keeping just in case
                    'finished_students' => $finishedCount,
                    'total_students' => $totalStudents,
                    'percentage' => $percentage,
                    'start_time' => \Carbon\Carbon::parse($exam->start_time)->format('H:i'),
                    'end_time' => \Carbon\Carbon::parse($exam->end_time)->format('H:i')
                ];
            });

        // 3. Upcoming Exams
        $upcoming_exams = \App\Models\Exam::where('teacher_id', $teacher->id)
            ->where('status', 'scheduled')
            ->where(function($q) {
                $q->whereDate('date', '>', now())
                  ->orWhere(function($sq) {
                      $sq->whereDate('date', now())
                         ->whereTime('start_time', '>', now()->format('H:i'));
                  });
            })
            ->orderBy('date')->orderBy('start_time')
            ->limit(5)
            ->get()
            ->map(function($exam) {
                $date = \Carbon\Carbon::parse($exam->date);
                return [
                    'name' => $exam->name,
                    'class' => $exam->classrooms->pluck('name')->join(', '),
                    'date_formatted' => $date->translatedFormat('d M Y'),
                    'day' => $date->format('d'),
                    'month' => $date->translatedFormat('M'),
                    'time' => \Carbon\Carbon::parse($exam->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($exam->end_time)->format('H:i')
                ];
            });
            
        // 4. Recent Activity (Latest 5 submissions)
        $recent_activities = \App\Models\ExamAttempt::whereHas('exam', function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
        ->whereNotNull('submitted_at')
        ->with(['student.user', 'exam'])
        ->orderBy('submitted_at', 'desc')
        ->limit(5)
        ->get()
        ->map(function($attempt) {
            return [
                'action' => "{$attempt->student->user->name} menyelesaikan {$attempt->exam->name}",
                'time' => $attempt->submitted_at->diffForHumans(),
                'type' => 'success',
                'icon' => 'check-circle' // Identifying icon type
            ];
        });
        
        // Add specific recent activity for Created Exams?
        $recent_exams = \App\Models\Exam::where('teacher_id', $teacher->id)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get()
            ->map(function($exam) {
                 return [
                    'action' => "Anda membuat ujian: {$exam->name}",
                    'time' => $exam->created_at->diffForHumans(),
                    'type' => 'info',
                    'icon' => 'plus-circle',
                    'sort_time' => $exam->created_at
                 ];
            });

        // Merge and sort activities maybe? For now let's stick to students submitting exams as primary activity.

        return view('teacher.dashboard', [
            'stats' => $stats,
            'ongoing_exams' => $ongoing_exams,
            'upcoming_exams' => $upcoming_exams,
            'recent_activities' => $recent_activities,
            'greeting' => $this->getGreeting()
        ])->layout('layouts.teacher');
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
