<?php

namespace App\Livewire\Teacher\Monitoring;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        // Dummy Active Exams Data for Teacher
        $activeExams = [
            [
                'id' => 1,
                'name' => 'Ujian Harian Matematika',
                'class' => 'XI IPA 1',
                'subject' => 'Matematika',
                'start_time' => '08:00',
                'end_time' => '09:30',
                'total_students' => 32,
                'working' => 25,
                'finished' => 6,
                'not_started' => 1,
            ],
            // Additional dummy data relevant to the logged-in teacher
        ];

        return view('teacher.monitoring.index', [
            'activeExams' => $activeExams
        ])->extends('layouts.teacher')->section('content');
    }
}
