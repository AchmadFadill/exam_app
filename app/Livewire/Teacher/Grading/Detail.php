<?php

namespace App\Livewire\Teacher\Grading;

use Livewire\Component;

class Detail extends Component
{
    public $examId;
    public $studentId = 1;
    public $pgScore = 45; // Dummy PG Score
    public $maxPgScore = 50; // Max PG Score

    public $pgAnswers = [
        [
            'no' => 1,
            'question' => 'Apa fungsi utama stomata pada daun?',
            'student_answer' => 'A. Pertukaran gas',
            'key' => 'A. Pertukaran gas',
            'is_correct' => true
        ],
        [
            'no' => 2,
            'question' => 'Bagian sel yang berfungsi sebagai pembangkit energi adalah...',
            'student_answer' => 'C. Ribosom',
            'key' => 'B. Mitokondria',
            'is_correct' => false
        ],
        [
            'no' => 3,
            'question' => 'Hewan yang berkembang biak dengan cara bertelur disebut...',
            'student_answer' => 'A. Ovipar',
            'key' => 'A. Ovipar',
            'is_correct' => true
        ]
    ];
    
    // Essay Answers
    public $answers = [
        [
            'id' => 1,
            'question' => 'Jelaskan proses fotosintesis pada tumbuhan!',
            'student_answer' => 'Fotosintesis adalah proses tumbuhan mengubah energi cahaya menjadi energi kimia. Tumbuhan menggunakan air dan karbon dioksida untuk menghasilkan glukosa dan oksigen dengan bantuan klorofil.',
            'key' => 'Poin penting: Energi cahaya -> Kimia, Air + CO2 -> Glukosa + O2, Peran Klorofil.',
            'max_score' => 20,
            'score' => 18,
            'feedback' => 'Jawaban sudah cukup lengkap.'
        ],
        [
             'id' => 2,
             'question' => 'Apa dampak pemanasan global bagi ekosistem laut?',
             'student_answer' => 'Es kutub mencair dan air laut naik.',
             'key' => 'Naiknya suhu air, pemutihan karang (coral bleaching), naiknya permukaan laut, terganggunya rantai makanan.',
             'max_score' => 15,
             'score' => 5,
             'feedback' => 'Kurang lengkap, jelaskan tentang coral bleaching.'
        ]
    ];

    public function render()
    {
        return view('teacher.grading.detail', [
            'student_name' => 'Aditya Pratama',
            'grade' => 'XI IPA 1',
            'current_score' => 85, // Total score so far
            'pg_score' => $this->pgScore,
            'max_pg_score' => $this->maxPgScore,
            'pg_answers' => $this->pgAnswers
        ])->extends('layouts.teacher')->section('content');
    }

    public function saveScore($index)
    {
        // Save logic
    }
}
