<?php

namespace App\Livewire\Teacher\Grading;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        // Dummy exams that need grading
        $exams = [
             [
                'id' => 1,
                'name' => 'Ujian Akhir Semester Bahasa Indonesia',
                'class' => 'XII IPA 1',
                'date' => '2025-12-20',
                'status' => 'needs_grading', // needs_grading, graded
                'pending_count' => 5, // Essays to grade
                'total_students' => 30,
                'is_published' => false,
            ],
            [
                'id' => 2,
                'name' => 'Ujian Harian PKN',
                'class' => 'X IPS 2',
                'date' => '2025-12-18',
                'status' => 'graded',
                'pending_count' => 0,
                'total_students' => 28,
                'is_published' => true,
            ]
        ];

        return view('teacher.grading.index', [
            'exams' => $exams
        ])->extends('layouts.teacher')->section('content');
    }
}
