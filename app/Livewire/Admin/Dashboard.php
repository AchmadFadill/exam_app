<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // Global Stats
        $stats = [
            'total_students' => \App\Models\Student::count(),
            'total_teachers' => \App\Models\Teacher::count(),
            'total_exams' => \App\Models\Exam::count(),
            'total_questions' => \App\Models\Question::count(),
            'active_exams_count' => 0, // Will update below
        ];

        // System Health (Simulated/Partial Real)
        $diskFree = disk_free_space(base_path());
        $diskTotal = disk_total_space(base_path());
        $diskUsage = round((($diskTotal - $diskFree) / $diskTotal) * 100);
        
        $system_health = [
            'cpu_load' => rand(10, 40), // Simulated
            'ram_usage' => rand(30, 60), // Simulated
            'disk_space' => $diskUsage,
            'uptime' => 'Online', // Simplified
            'status' => 'Healthy',
        ];

        // Active Exams Feed
        $active_exams_query = \App\Models\Exam::where('status', 'scheduled')
            ->whereDate('date', now())
            ->whereTime('start_time', '<=', now()->format('H:i'))
            ->whereTime('end_time', '>=', now()->format('H:i'))
            ->with(['teacher', 'subject']);

        $stats['active_exams_count'] = $active_exams_query->count();

        $active_exams = $active_exams_query->get()->map(function($exam) {
            $totalStudents = $exam->classrooms->sum(function($c) { return $c->students()->count(); });
            $finishedCount = $exam->attempts()->whereIn('status', ['submitted', 'graded'])->count();
            $inProgressCount = $exam->attempts()->where('status', 'in_progress')->count();
            $progress = $totalStudents > 0 ? round(($finishedCount / $totalStudents) * 100) : 0;

            return [
                'subject' => $exam->subject->name,
                'class' => $exam->classrooms->pluck('name')->join(', '),
                'teacher' => $exam->teacher->user->name,
                'progress' => $progress,
                'students_online' => $inProgressCount,
                'total_students' => $totalStudents,
            ];
        });

        // Security Alerts Feed (Using recent exam attempts as proxy for activity)
        $alerts = \App\Models\ExamAttempt::with(['student.user', 'student.classroom'])
            ->where('updated_at', '>=', now()->subDay())
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($attempt) {
                $event = match($attempt->status) {
                    'in_progress' => 'Memulai Ujian',
                    'submitted' => 'Mengumpulkan Ujian',
                    'graded' => 'Nilai Keluar',
                    default => 'Aktivitas Ujian'
                };
                
                return [
                    'user' => $attempt->student->user->name,
                    'class' => $attempt->student->classroom->name,
                    'event' => $event,
                    'time' => $attempt->updated_at->diffForHumans(),
                    'severity' => $attempt->status === 'submitted' ? 'success' : 'info',
                ];
            });

        return view('admin.dashboard', [
            'greeting' => $this->getGreeting(),
            'stats' => $stats,
            'system_health' => $system_health,
            'active_exams' => $active_exams,
            'alerts' => $alerts,
        ])->layout('layouts.admin', ['title' => 'Dashboard']);
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
