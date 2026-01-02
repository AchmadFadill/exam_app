<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // Global Stats Dummy Data
        $stats = [
            'total_students' => 1240,
            'total_teachers' => 86,
            'total_exams' => 42,
            'total_questions' => 12500,
            'active_exams_count' => 5,
        ];

        // System Health Dummy Data
        $system_health = [
            'cpu_load' => 24,
            'ram_usage' => 45,
            'disk_space' => 78,
            'uptime' => '12 Hari, 4 Jam',
            'status' => 'Healthy',
        ];

        // Active Exams Feed
        $active_exams = [
            [
                'subject' => 'Matematika Wajib',
                'class' => 'XII IPA 1',
                'teacher' => 'Pak Budi',
                'progress' => 85,
                'students_online' => 32,
                'total_students' => 34,
            ],
            [
                'subject' => 'Bahasa Inggris',
                'class' => 'X IPS 2',
                'teacher' => 'Bu Siti',
                'progress' => 42,
                'students_online' => 28,
                'total_students' => 30,
            ],
        ];

        // Security Alerts Feed
        $alerts = [
            [
                'user' => 'Andi Wijaya',
                'class' => 'XII IPA 1',
                'event' => 'Pindah Tab Aler',
                'time' => '2 menit yang lalu',
                'severity' => 'critical',
            ],
            [
                'user' => 'Siska Pratama',
                'class' => 'X IPS 2',
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
        ])->extends('layouts.admin')->section('content');
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
