<?php

namespace App\Livewire\Admin\Reports;

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
        // Dummy Detail Data
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
            ['name' => 'Ahmad Fadhil', 'score' => 98, 'status' => 'Lulus', 'started_at' => '08:00', 'submitted_at' => '08:45'],
            ['name' => 'Budi Santoso', 'score' => 85, 'status' => 'Lulus', 'started_at' => '08:05', 'submitted_at' => '09:00'],
            ['name' => 'Citra Dewi', 'score' => 90, 'status' => 'Lulus', 'started_at' => '08:00', 'submitted_at' => '08:50'],
            ['name' => 'Doni Pratama', 'score' => 65, 'status' => 'Remedial', 'started_at' => '08:10', 'submitted_at' => '09:15'],
            ['name' => 'Eka Putri', 'score' => 75, 'status' => 'Lulus', 'started_at' => '08:00', 'submitted_at' => '08:55'],
            // Add more dummy data as needed
        ];

        return view('admin.reports.detail', [
            'exam' => $exam,
            'students' => $students
        ])->extends('layouts.admin')->section('content');
    }
}
