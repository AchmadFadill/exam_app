<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class ManageExam extends Component
{
    public function render()
    {
        // Dummy Exams Data (Replicated from Teacher for Admin)
        $exams = [
            [
                'id' => 1,
                'name' => 'Ujian Harian Matematika',
                'subject' => 'Matematika',
                'class' => 'XI IPA 1',
                'date' => '2025-12-23',
                'duration' => 90,
                'status' => 'scheduled', // scheduled, ongoing, completed
                'questions_count' => 30,
            ],
            [
                'id' => 2,
                'name' => 'Ujian Akhir Semester Fisika',
                'subject' => 'Fisika',
                'class' => 'XII IPA 2',
                'date' => '2025-12-22',
                'duration' => 120,
                'status' => 'ongoing',
                'questions_count' => 45,
            ],
            [
                'id' => 3,
                'name' => 'Kuis Sejarah Indonesia',
                'subject' => 'Sejarah',
                'class' => 'X IPS 1',
                'date' => '2025-12-20',
                'duration' => 45,
                'status' => 'completed',
                'questions_count' => 20,
            ],
        ];

        return view('admin.manage-exam', [
            'exams' => $exams
        ])->extends('layouts.admin')->section('content');
    }
}
