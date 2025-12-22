<?php

namespace App\Livewire\Teacher\QuestionBank;

use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $subjectFilter = '';
    public $typeFilter = '';

    public function render()
    {
        // Dummy Data Simulation
        $questions = collect([
            [
                'id' => 1,
                'q' => 'Apa ibukota Indonesia?',
                'subject' => 'Geografi',
                'type' => 'Pilihan Ganda',
                'created_at' => '2025-12-20 10:00:00'
            ],
            [
                'id' => 2,
                'q' => 'Jelaskan proses fotosintesis!',
                'subject' => 'Biologi',
                'type' => 'Essay',
                'created_at' => '2025-12-21 09:15:00'
            ],
            [
                'id' => 3,
                'q' => 'Hitunglah luas lingkaran jika r=7cm',
                'subject' => 'Matematika',
                'type' => 'Pilihan Ganda',
                'created_at' => '2025-12-21 14:20:00'
            ],
            [
                'id' => 4,
                'q' => 'Siapakah presiden pertama RI?',
                'subject' => 'Sejarah',
                'type' => 'Pilihan Ganda',
                'created_at' => '2025-12-22 08:00:00'
            ],
        ]);

        if ($this->search) {
            $questions = $questions->filter(function ($item) {
                return str_contains(strtolower($item['q']), strtolower($this->search));
            });
        }

        if ($this->subjectFilter) {
            $questions = $questions->where('subject', $this->subjectFilter);
        }

        if ($this->typeFilter) {
            $questions = $questions->where('type', $this->typeFilter);
        }

        return view('teacher.question-bank.index', [
            'questions' => $questions
        ])->extends('layouts.teacher')->section('content');
    }
}
