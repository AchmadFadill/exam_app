<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class ManageExam extends Component
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
        if (!empty($this->selectedExams)) {
            \App\Models\Exam::destroy($this->selectedExams);
            $this->dispatch('notify', ['message' => 'Ujian terpilih berhasil dihapus!']);
        }
        
        $this->showBulkDeleteModal = false;
        $this->selectedExams = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Select all IDs (Warning: if many exams, this might be heavy. 
            // Better to select current page, but for now select all is expected behavior for "Select All")
            $this->selectedExams = \App\Models\Exam::pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedExams = [];
        }
    }
    use \Livewire\WithPagination;

    // ... (keep existing properties)

    public function render()
    {
        $examsQuery = \App\Models\Exam::with(['subject', 'classrooms', 'questions'])
            ->latest();

        $exams = $examsQuery->paginate(10)->through(function ($exam) {
            
            // Dynamic Status Calculation
            $status = $exam->status;
            if ($status === 'scheduled') {
                $now = now();
                $date = $exam->date->format('Y-m-d');
                // Handle potential null times if draft
                $start = $exam->start_time ? \Carbon\Carbon::parse($date . ' ' . $exam->start_time) : null;
                $end = $exam->end_time ? \Carbon\Carbon::parse($date . ' ' . $exam->end_time) : null;

                if ($start && $end) {
                    if ($now->between($start, $end)) {
                        $status = 'ongoing';
                    } elseif ($now->gt($end)) {
                        $status = 'completed';
                    }
                }
            }

            return [
                'id' => $exam->id,
                'name' => $exam->name,
                'subject' => $exam->subject->name ?? '-',
                'class' => $exam->classrooms->pluck('name')->join(', '), // Comma separated classes
                'date' => $exam->date->format('Y-m-d'),
                'start_time' => $exam->start_time,
                'end_time' => $exam->end_time,
                'token' => $exam->token,
                'duration' => $exam->duration_minutes,
                'default_score' => $exam->default_score,
                'status' => $status,
                'questions_count' => $exam->questions->count(),
            ];
        });

        return view('admin.manage-exam', [
            'exams' => $exams
        ])->layout('layouts.admin', ['title' => 'Kelola Ujian']);
    }
}
