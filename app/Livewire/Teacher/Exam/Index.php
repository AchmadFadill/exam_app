<?php

namespace App\Livewire\Teacher\Exam;

use Livewire\Component;

class Index extends Component
{
    // Bulk Action States
    public $selectedExams = [];
    public $selectAll = false;
    public $showBulkDeleteModal = false;

    public function openBulkDeleteModal()
    {
        if (empty($this->selectedExams)) {
            $this->dispatch('notify', ['message' => 'Pilih ujian terlebih dahulu!', 'type' => 'error']);
            return;
        }
        $this->showBulkDeleteModal = true;
    }

    public function bulkDelete()
    {
        // Dummy bulk delete logic
        $this->showBulkDeleteModal = false;
        $this->selectedExams = [];
        $this->selectAll = false;
        $this->dispatch('notify', ['message' => 'Ujian terpilih berhasil dihapus!']);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedExams = [1, 2, 3];
        } else {
            $this->selectedExams = [];
        }
    }
    public function render()
    {
        // Dummy Exams Data
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

        return view('teacher.exam.index', [
            'exams' => $exams
        ])->extends('layouts.teacher')->section('content');
    }
}
