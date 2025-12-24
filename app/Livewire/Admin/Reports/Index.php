<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        // Dummy Report Data
        $results = [
            [
                'id' => 1,
                'exam_name' => 'Ujian Akhir Semester Matematika',
                'class' => 'XI IPA 1',
                'subject' => 'Matematika',
                'date' => '23 Des 2025',
                'avg_score' => 82.5,
                'highest' => 98,
                'lowest' => 65,
                'participants' => 32
            ],
            [
                'id' => 2,
                'exam_name' => 'Ujian Akhir Semester Fisika',
                'class' => 'XI IPA 1',
                'subject' => 'Fisika',
                'date' => '22 Des 2025',
                'avg_score' => 75.0,
                'highest' => 90,
                'lowest' => 55,
                'participants' => 32
            ],
            [
                'id' => 3,
                'exam_name' => 'Kuis Sejarah',
                'class' => 'X IPS 2',
                'subject' => 'Sejarah',
                'date' => '21 Des 2025',
                'avg_score' => 78.0,
                'highest' => 95,
                'lowest' => 50,
                'participants' => 28
            ]
        ];

        return view('admin.reports.index', [
            'results' => $results
        ])->extends('layouts.admin')->section('content');
    }
}
