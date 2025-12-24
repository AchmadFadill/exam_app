<?php

namespace App\Livewire\Admin\Monitoring;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        // Dummy Active Exams Data
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
            [
                'id' => 2,
                'name' => 'Ujian Akhir Semester Fisika',
                'class' => 'XII IPA 2',
                'subject' => 'Fisika',
                'start_time' => '08:00',
                'end_time' => '10:00',
                'total_students' => 30,
                'working' => 28,
                'finished' => 0,
                'not_started' => 2,
            ],
             [
                'id' => 4,
                'name' => 'Kuis Biologi',
                'class' => 'X IPA 3',
                'subject' => 'Biologi',
                'start_time' => '09:00',
                'end_time' => '09:45',
                'total_students' => 35,
                'working' => 30,
                'finished' => 5,
                'not_started' => 0,
            ],
        ];

        return view('admin.monitoring.index', [
            'activeExams' => $activeExams
        ])->extends('layouts.admin')->section('content');
    }
}
