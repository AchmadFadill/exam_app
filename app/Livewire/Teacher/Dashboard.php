<?php

namespace App\Livewire\Teacher;

use App\Enums\ExamAttemptStatus;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $teacher = $user->teacher()->with([
            'classroom:id,name,teacher_id',
            'subjects:id,name',
        ])->first();
        
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
                'greeting' => $this->getGreeting(),
                'homeroom_class' => null,
                'taught_subjects' => [],
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
        })->where('status', ExamAttemptStatus::Submitted->value)->count();

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

        // 2. Ongoing Exams - OPTIMIZED
        $ongoing_exams = \App\Models\Exam::where('teacher_id', $teacher->id)
            ->where('status', 'scheduled')
            ->whereDate('date', now())
            ->whereTime('start_time', '<=', now()->format('H:i'))
            ->whereTime('end_time', '>=', now()->format('H:i'))
            ->with([
                'subject',
                'classrooms' => function($q) {
                    $q->withCount('students');
                }
            ])
            ->withCount([
                'attempts as in_progress_count' => function($q) {
                    $q->where('status', ExamAttemptStatus::InProgress->value);
                },
                'attempts as finished_count' => function($q) {
                    $q->whereIn('status', [
                        ExamAttemptStatus::Submitted->value,
                        ExamAttemptStatus::Graded->value,
                        ExamAttemptStatus::Completed->value,
                        ExamAttemptStatus::TimedOut->value,
                    ]);
                }
            ])
            ->get()
            ->map(function($exam) {
                $totalStudents = $exam->classrooms->sum('students_count');
                $finishedCount = $exam->finished_count;
                $percentage = $totalStudents > 0 ? round(($finishedCount / $totalStudents) * 100) : 0;

                return [
                    'id' => $exam->id,
                    'status' => $exam->status,
                    'subject' => $exam->subject->name,
                    'class' => $exam->classrooms->pluck('name')->join(', '),
                    'name' => $exam->name,
                    'participants' => $exam->in_progress_count,
                    'students_online' => $exam->in_progress_count,
                    'finished_students' => $finishedCount,
                    'total_students' => $totalStudents,
                    'percentage' => $percentage,
                    'progress' => $percentage,
                    'start_time' => \Carbon\Carbon::parse($exam->start_time)->format('H:i'),
                    'end_time' => \Carbon\Carbon::parse($exam->end_time)->format('H:i'),
                    'monitor_url' => route('teacher.monitoring.detail', $exam->id),
                ];
            });

        // 3. Upcoming Exams - OPTIMIZED
        $upcoming_exams = \App\Models\Exam::where('teacher_id', $teacher->id)
            ->where('status', 'scheduled')
            ->where(function($q) {
                $q->whereDate('date', '>', now())
                  ->orWhere(function($sq) {
                      $sq->whereDate('date', now())
                         ->whereTime('start_time', '>', now()->format('H:i'));
                  });
            })
            ->with('classrooms:id,name')
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
            'greeting' => $this->getGreeting(),
            'homeroom_class' => $teacher->classroom?->name,
            'taught_subjects' => $teacher->subjects->pluck('name')->values()->all(),
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
