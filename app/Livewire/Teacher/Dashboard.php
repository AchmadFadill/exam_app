<?php

namespace App\Livewire\Teacher;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('teacher.dashboard', [
            'stats' => [
                'active_exams' => 2,
                'completed_exams' => 15,
                'total_students' => 120,
                'questions_count' => 450
            ],
            'recent_activities' => [
                ['action' => 'Ujian Matematika X-A selesai', 'time' => '10 menit yang lalu', 'type' => 'success'],
                ['action' => 'Budi mengumpulkan tugas', 'time' => '25 menit yang lalu', 'type' => 'info'],
                ['action' => 'Soal baru ditambahkan ke Bank Soal', 'time' => '1 jam yang lalu', 'type' => 'neutral'],
                ['action' => 'Ujian Fisika XII dimulai', 'time' => '2 jam yang lalu', 'type' => 'warning'],
            ],
            'ongoing_exams' => [
                ['name' => 'Ujian Harian Matematika', 'class' => 'XI IPA 1', 'progress' => '24/30 Siswa Selesai', 'end_time' => '10:30 WIB'],
                ['name' => 'Kuis Sejarah', 'class' => 'X IPS 2', 'progress' => 'Baru dimulai', 'end_time' => '11:00 WIB'],
            ]
        ])->extends('layouts.teacher')->section('content');
    }
}
