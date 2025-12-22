<?php

namespace App\Livewire\Teacher\Report;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        // Dummy Report Data
        $results = [
            [
                'exam_name' => 'Ujian Harian Matematika',
                'class' => 'XI IPA 1',
                'date' => '23 Des 2025',
                'avg_score' => 82.5,
                'highest' => 98,
                'lowest' => 65,
                'participants' => 32
            ],
            [
                'exam_name' => 'Kuis Sejarah',
                'class' => 'X IPS 2',
                'date' => '21 Des 2025',
                'avg_score' => 78.0,
                'highest' => 95,
                'lowest' => 50,
                'participants' => 28
            ]
        ];

        return view('teacher.report.index', [
            'results' => $results
        ])->extends('layouts.teacher')->section('content');
    }
}
