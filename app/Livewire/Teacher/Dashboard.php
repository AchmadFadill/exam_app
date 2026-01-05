<?php

namespace App\Livewire\Teacher;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // Dummy data for stats
        $stats = [
            'active_exams' => 3,
            'completed_exams' => 45,
            'grading_needed' => 12,
            'questions_count' => 1250
        ];

        // Dummy data for Ongoing Exams (Live)
        $ongoing_exams = [
            [
                'id' => 1,
                'name' => 'Ujian Akhir Semester Matematika',
                'class' => 'XII IPA 1',
                'subject' => 'Matematika Wajib',
                'total_students' => 32,
                'finished_students' => 18,
                'start_time' => '07:30',
                'end_time' => '09:30',
                'percentage' => 56 // (18/32)*100
            ],
            [
                'id' => 2,
                'name' => 'Kuis Fisika Bab 3',
                'class' => 'XI IPA 2',
                'subject' => 'Fisika',
                'total_students' => 30,
                'finished_students' => 5,
                'start_time' => '08:00',
                'end_time' => '09:00',
                'percentage' => 16
            ],
            [
                'id' => 3,
                'name' => 'Ulangan Harian Biologi',
                'class' => 'X IPA 3',
                'subject' => 'Biologi',
                'total_students' => 28,
                'finished_students' => 28,
                'start_time' => '07:30',
                'end_time' => '09:00',
                'percentage' => 100
            ]
        ];

        // Dummy data for Upcoming Exams
        $upcoming_exams = [
            [
                'name' => 'Ujian Kimia Dasar',
                'class' => 'X IPA 1',
                'date' => 'Besok, 3 Jan',
                'time' => '08:00 - 10:00'
            ],
            [
                'name' => 'Kuis Sejarah Indonesia',
                'class' => 'XI IPS 1',
                'date' => 'Senin, 6 Jan',
                'time' => '10:00 - 11:30'
            ],
            [
                'name' => 'Ujian Susulan Bahasa Inggris',
                'class' => 'XII Bahasa',
                'date' => 'Selasa, 7 Jan',
                'time' => '13:00 - 14:30'
            ]
        ];

        // Dummy data for Recent Activities
        $recent_activities = [
            ['action' => 'Ujian Matematika X-A selesai', 'time' => '10 menit yang lalu', 'type' => 'success'],
            ['action' => 'Soal Essay Fisika belum dinilai', 'time' => '25 menit yang lalu', 'type' => 'warning'],
            ['action' => 'Bank Soal Kimia diperbarui', 'time' => '1 jam yang lalu', 'type' => 'info'],
            ['action' => 'Jadwal Ujian Sejarah dibuat', 'time' => '2 jam yang lalu', 'type' => 'neutral'],
        ];

        return view('teacher.dashboard', [
            'stats' => $stats,
            'ongoing_exams' => $ongoing_exams,
            'upcoming_exams' => $upcoming_exams,
            'recent_activities' => $recent_activities,
            'greeting' => $this->getGreeting()
        ])->extends('layouts.teacher')->section('content');
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
