<?php

namespace App\Livewire\Teacher\Report;

use Livewire\Component;

class Detail extends Component
{
    public $examId;

    public function mount($id)
    {
        $this->examId = $id;
    }

    public function render()
    {
        // Dummy Detail Data (Reused logic from Admin, usually this comes from DB)
        $exam = [
            'id' => 1,
            'exam_name' => 'Ujian Akhir Semester Matematika',
            'class' => 'XI IPA 1',
            'subject' => 'Matematika',
            'date' => '23 Des 2025',
            'avg_score' => 82.5,
            'highest' => 98,
            'lowest' => 65,
            'participants' => 32
        ];

        $students = [
            ['name' => 'Ahmad Fadhil', 'score' => 98, 'status' => 'Lulus', 'submitted_at' => '08:45'],
            ['name' => 'Budi Santoso', 'score' => 85, 'status' => 'Lulus', 'submitted_at' => '09:00'],
            ['name' => 'Citra Dewi', 'score' => 90, 'status' => 'Lulus', 'submitted_at' => '08:50'],
            ['name' => 'Doni Pratama', 'score' => 65, 'status' => 'Remedial', 'submitted_at' => '09:15'],
            ['name' => 'Eka Putri', 'score' => 75, 'status' => 'Lulus', 'submitted_at' => '08:55'],
        ];

        $most_failed_questions = [
            [
                'number' => 12,
                'text' => 'Berapakah hasil dari integral sin(x) dx?',
                'failed_count' => 15,
                'failed_percentage' => 46,
                'correct_answer' => '-cos(x) + C'
            ],
            [
                'number' => 5,
                'text' => 'Tentukan turunan pertama dari f(x) = x³ - 2x + 5.',
                'failed_count' => 10,
                'failed_percentage' => 31,
                'correct_answer' => '3x² - 2'
            ],
            [
                'number' => 28,
                'text' => 'Jika log 2 = a dan log 3 = b, maka log 18 adalah...',
                'failed_count' => 8,
                'failed_percentage' => 25,
                'correct_answer' => 'a + 2b'
            ],
        ];

        return view('teacher.report.detail', [
            'exam' => $exam,
            'students' => $students,
            'most_failed_questions' => $most_failed_questions
        ])->extends('layouts.teacher')->section('content');
    }
}
