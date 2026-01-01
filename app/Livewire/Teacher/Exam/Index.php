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

    public $exams = [];

    public function mount()
    {
        // Dummy Exams Data (Initialized in mount)
        $this->exams = [
            [
                'id' => 1,
                'name' => 'Ujian Harian Matematika',
                'subject' => 'Matematika',
                'class' => 'XI IPA 1',
                'date' => '2025-12-23',
                'start_time' => '08:00',
                'end_time' => '09:30',
                'duration' => 90,
                'status' => 'scheduled',
                'questions_count' => 30,
            ],
            [
                'id' => 2,
                'name' => 'Ujian Akhir Semester Fisika',
                'subject' => 'Fisika',
                'class' => 'XII IPA 2',
                'date' => '2025-12-22',
                'start_time' => '08:00',
                'end_time' => '10:00',
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
                'start_time' => '08:00',
                'end_time' => '08:45',
                'duration' => 45,
                'status' => 'completed',
                'questions_count' => 20,
            ],
        ];
    }

    public function duplicateExam($id)
    {
        // Find the exam to duplicate
        $original = collect($this->exams)->firstWhere('id', $id);

        if ($original) {
            $newExam = $original;
            $newExam['id'] = count($this->exams) + 1; // Simple increment ID
            $newExam['name'] = 'Salinan - ' . $original['name'];
            $newExam['status'] = 'scheduled';
            $newExam['date'] = date('Y-m-d'); // Reset to today
            $newExam['token'] = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 5)); // New Token
            
            // Add to top of list
            array_unshift($this->exams, $newExam);

            $this->dispatch('notify', ['message' => 'Ujian berhasil diduplikasi!']);
        }
    }

    public function render()
    {
        return view('teacher.exam.index')
            ->extends('layouts.teacher')
            ->section('content');
    }
}
