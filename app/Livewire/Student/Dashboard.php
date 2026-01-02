<?php

namespace App\Livewire\Student;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // Dummy Data: Active Exams (Currently running)
        $active_exams = [
            [
                'id' => 1,
                'subject' => 'Matematika Wajib',
                'title' => 'Ujian Akhir Semester',
                'teacher' => 'Pak Budi Santoso',
                'start_time' => '07:30',
                'end_time' => '09:30',
                'duration' => 120, // minutes
                'questions_count' => 40,
                'status' => 'ongoing', // ongoing, not_started, finished
                'is_urgent' => true, // nearing deadline
            ],
            [
                'id' => 2,
                'subject' => 'Bahasa Indonesia',
                'title' => 'Ulangan Harian Bab 3',
                'teacher' => 'Bu Siti Aminah',
                'start_time' => '08:00',
                'end_time' => '10:00',
                'duration' => 90,
                'questions_count' => 30,
                'status' => 'ongoing',
                'is_urgent' => false,
            ]
        ];

        // Dummy Data: Upcoming Exams
        $upcoming_exams = [
            [
                'subject' => 'Fisika',
                'title' => 'Kuis Listrik Dinamis',
                'date' => 'Besok, 08:00 WIB',
                'class' => 'XII IPA 1'
            ],
            [
                'subject' => 'Kimia',
                'title' => 'Ujian Stoikiometri',
                'date' => 'Senin, 6 Jan • 07:30 WIB',
                'class' => 'XII IPA 1'
            ]
        ];

        // Dummy Data: Stats
        $stats = [
            'avg_score' => 85.5,
            'attendance' => 92,
            'completed_exams' => 12
        ];

        return view('student.dashboard', [
            'greeting' => $this->getGreeting(),
            'active_exams' => $active_exams,
            'upcoming_exams' => $upcoming_exams,
            'stats' => $stats
        ])->extends('layouts.student')->section('content');
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
