<?php

namespace App\Livewire\Teacher\Exam;

use Livewire\Component;

class Monitor extends Component
{
    // Simulate real-time polling
    public function render()
    {
        // Dummy Student Progress Data
        $students = [
            ['name' => 'Aditya Pratama', 'status' => 'working', 'progress' => '15/30', 'w' => '50%', 'tab_alert' => 0],
            ['name' => 'Bunga Citra', 'status' => 'working', 'progress' => '28/30', 'w' => '93%', 'tab_alert' => 1],
            ['name' => 'Chandra Wijaya', 'status' => 'completed', 'progress' => '30/30', 'w' => '100%', 'tab_alert' => 0],
            ['name' => 'Dewi Sartika', 'status' => 'not_started', 'progress' => '0/30', 'w' => '0%', 'tab_alert' => 0],
            ['name' => 'Eko Kurniawan', 'status' => 'working', 'progress' => '5/30', 'w' => '16%', 'tab_alert' => 4], // Warning
            ['name' => 'Fajar Santoso', 'status' => 'working', 'progress' => '20/30', 'w' => '66%', 'tab_alert' => 0],
             ['name' => 'Gita Gutawa', 'status' => 'working', 'progress' => '10/30', 'w' => '33%', 'tab_alert' => 0],
             ['name' => 'Hadi Sucipto', 'status' => 'completed', 'progress' => '30/30', 'w' => '100%', 'tab_alert' => 0],
             ['name' => 'Indah Permata', 'status' => 'working', 'progress' => '15/30', 'w' => '50%', 'tab_alert' => 2],
        ];

        return view('teacher.exam.monitor', [
            'students' => $students
        ])->extends('layouts.teacher')->section('content');
    }

    public function forceSubmit($studentId)
    {
        // Logic to force submit
    }
}
