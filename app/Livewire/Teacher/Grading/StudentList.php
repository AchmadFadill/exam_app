<?php

namespace App\Livewire\Teacher\Grading;

use Livewire\Component;

class StudentList extends Component
{
    public $examId;
    public $examName = 'Biologi Dasar - UTS';
    public $className = 'XI IPA 1';

    // Dummy Data
    public $students = [
        [
            'id' => 1,
            'name' => 'Aditya Pratama',
            'submitted_at' => '2025-12-25 09:30',
            'status' => 'Belum Dinilai',
            'score' => '-',
        ],
        [
            'id' => 2,
            'name' => 'Budi Santoso',
            'submitted_at' => '2025-12-25 09:35',
            'status' => 'Sudah Dinilai',
            'score' => 85,
        ],
        [
            'id' => 3,
            'name' => 'Citra Dewi',
            'submitted_at' => '2025-12-25 09:40',
            'status' => 'Belum Dinilai',
            'score' => '-',
        ],
    ];

    public function mount($exam)
    {
        $this->examId = $exam;
        // In real app, fetch exam details and participants here
    }

    public function render()
    {
        return view('teacher.grading.student-list')->extends('layouts.teacher')->section('content');
    }
}
