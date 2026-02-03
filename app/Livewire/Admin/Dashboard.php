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

        // Active Exams Feed - REAL DATA - OPTIMIZED
        $activeExamsQuery = \App\Models\Exam::where('status', 'scheduled')
            ->whereDate('date', now())
            ->whereTime('start_time', '<=', now()->format('H:i'))
            ->whereTime('end_time', '>=', now()->format('H:i'))
            ->with([
                'subject', 
                'classrooms' => function($q) {
                    $q->withCount('students');
                },
                'teacher.user', 
                'attempts' => function($q) {
                    $q->whereNotNull('started_at');
                }
            ])
            ->get();

        $active_exams = $activeExamsQuery->map(function($exam) {
            // Calculate total students assigned to this exam (via classrooms) - OPTIMIZED
            $totalStudents = $exam->classrooms->sum('students_count');
            
            // Students who have started (have an attempt record)
            $studentsOnline = $exam->attempts->count();
            
            // Calculate progress based on time elapsed
            $start = \Carbon\Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->start_time);
            $end = \Carbon\Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->end_time);
            $totalDuration = $start->diffInMinutes($end);
            $elapsed = $start->diffInMinutes(now());
            $progress = $totalDuration > 0 ? min(100, round(($elapsed / $totalDuration) * 100)) : 0;

            return [
                'subject' => $exam->subject->name ?? 'Unknown Subject',
                'class' => $exam->classrooms->pluck('name')->join(', '),
                'teacher' => $exam->teacher->user->name ?? 'Unknown Teacher',
                'progress' => $progress,
                'students_online' => $studentsOnline,
                'total_students' => $totalStudents,
            ];
        })->toArray();

        // Update active exams count
        $stats['active_exams_count'] = count($active_exams);

        // Security Alerts Feed
        $alerts = [
            [
                'user' => 'Andi Wijaya',
                'class' => 'XII IPA 1',
                'exam' => 'Matematika Wajib',
                'event' => 'Pindah Tab Aler',
                'time' => '2 menit yang lalu',
                'severity' => 'critical',
            ],
            [
                'user' => 'Siska Pratama',
                'class' => 'X IPS 2',
                'exam' => 'Bahasa Inggris',
                'event' => 'Keluar Fullscreen',
                'time' => '15 menit yang lalu',
                'severity' => 'warning',
            ],
        ];

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
