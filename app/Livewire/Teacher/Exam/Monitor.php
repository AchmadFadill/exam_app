<?php

namespace App\Livewire\Teacher\Exam;

use Livewire\Component;

class Monitor extends Component
{
    public $examId;

    public function mount($id)
    {
        $this->examId = $id;
    }

    // Simulate real-time polling
    public function render()
    {
        // Dummy Student Progress Data
        $students = [
            ['name' => 'Aditya Pratama', 'class' => 'XI IPA 1', 'status' => 'working', 'progress' => '15/30', 'w' => '50%', 'tab_alert' => 0],
            ['name' => 'Bunga Citra', 'class' => 'XI IPA 2', 'status' => 'working', 'progress' => '28/30', 'w' => '93%', 'tab_alert' => 1],
            ['name' => 'Chandra Wijaya', 'class' => 'XI IPA 1', 'status' => 'completed', 'progress' => '30/30', 'w' => '100%', 'tab_alert' => 0],
            ['name' => 'Dewi Sartika', 'class' => 'XI IPS 1', 'status' => 'not_started', 'progress' => '0/30', 'w' => '0%', 'tab_alert' => 0],
            ['name' => 'Eko Kurniawan', 'class' => 'XI IPA 2', 'status' => 'working', 'progress' => '5/30', 'w' => '16%', 'tab_alert' => 4], // Warning
            ['name' => 'Fajar Santoso', 'class' => 'XI IPS 1', 'status' => 'working', 'progress' => '20/30', 'w' => '66%', 'tab_alert' => 0],
             ['name' => 'Gita Gutawa', 'class' => 'XI IPA 1', 'status' => 'working', 'progress' => '10/30', 'w' => '33%', 'tab_alert' => 0],
             ['name' => 'Hadi Sucipto', 'class' => 'XI IPA 2', 'status' => 'completed', 'progress' => '30/30', 'w' => '100%', 'tab_alert' => 0],
             ['name' => 'Indah Permata', 'class' => 'XI IPS 1', 'status' => 'working', 'progress' => '15/30', 'w' => '50%', 'tab_alert' => 2],
        ];

        $live_logs = [
            ['time' => '08:49:10', 'student' => 'Aditya Pratama', 'activity' => 'Menjawab Soal #15', 'type' => 'info'],
            ['time' => '08:48:05', 'student' => 'Eko Kurniawan', 'activity' => 'Keluar Fullscreen', 'type' => 'warning'],
            ['time' => '08:47:30', 'student' => 'Bunga Citra', 'activity' => 'Menjawab Soal #28', 'type' => 'info'],
            ['time' => '08:46:15', 'student' => 'Chandra Wijaya', 'activity' => 'Menyelesaikan Ujian', 'type' => 'success'],
            ['time' => '08:45:00', 'student' => 'Dewi Sartika', 'activity' => 'Masuk ke Halaman Ujian', 'type' => 'primary'],
        ];

        return view('teacher.exam.monitor', [
            'students' => $students,
            'live_logs' => $live_logs
        ])->extends('layouts.teacher')->section('content');
    }

    public function forceSubmit($studentId)
    {
        // Logic to force submit
    }
}
