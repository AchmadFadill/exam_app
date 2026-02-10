<?php

namespace App\Livewire\Teacher\Exam;

use App\Actions\Exam\DuplicateExamAction;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    // Bulk Action States
    public $selectedExams = [];
    public $selectAll = false;
    public $showBulkDeleteModal = false;
    
    // Individual Delete States
    public $showDeleteModal = false;
    public $selectedExamId = null;

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
        try {
            $exams = Exam::whereIn('id', $this->selectedExams)->get();

            foreach ($exams as $exam) {
                Gate::authorize('delete', $exam);
            }

            $exams->each->delete();
            
            $this->showBulkDeleteModal = false;
            $this->selectedExams = [];
            $this->selectAll = false;
            $this->dispatch('notify', ['message' => 'Ujian terpilih berhasil dihapus!']);
        } catch (\Exception $e) {
            $this->showBulkDeleteModal = false;
            $this->dispatch('notify', ['message' => 'Gagal menghapus ujian: ' . $e->getMessage(), 'type' => 'error']);
        }
    }
    
    public function openDeleteModal($id)
    {
        $exam = Exam::findOrFail($id);
        Gate::authorize('delete', $exam);

        $this->selectedExamId = $id;
        $this->showDeleteModal = true;
    }
    
    public function deleteExam()
    {
        if ($this->selectedExamId) {
            try {
                $exam = Exam::find($this->selectedExamId);
                if ($exam) {
                    Gate::authorize('delete', $exam);
                    $exam->delete();
                    $this->dispatch('notify', ['message' => 'Ujian berhasil dihapus!']);
                } else {
                    $this->dispatch('notify', ['message' => 'Ujian tidak ditemukan.', 'type' => 'error']);
                }
            } catch (\Exception $e) {
                // Check if it's a constraint violation
                if (str_contains($e->getMessage(), 'ConstraintViolation')) {
                     $this->dispatch('notify', ['message' => 'Gagal: Ujian ini memiliki data terkait yang tidak bisa dihapus.', 'type' => 'error']);
                } else {
                     $this->dispatch('notify', ['message' => 'Gagal menghapus: ' . $e->getMessage(), 'type' => 'error']);
                }
            }
            
            $this->showDeleteModal = false;
            $this->selectedExamId = null;
        }
    }

    public function duplicateExam($id)
    {
        $original = Exam::with(['questions', 'classrooms'])->findOrFail($id);
        Gate::authorize('view', $original);

        app(DuplicateExamAction::class)->execute($original);

        $this->dispatch('notify', ['message' => 'Ujian berhasil diduplikasi!']);
    }

    private function getExamsQuery()
    {
        $user = Auth::user();
        $teacherId = $user->isTeacher() ? $user->teacher->id : null;

        $query = Exam::with(['subject', 'classrooms', 'questions', 'teacher.user'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc');

        // Filter by teacher if not admin
        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }
        return $query;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedExams = $this->getExamsQuery()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedExams = [];
        }
    }

    public function updatedSelectedExams()
    {
        $totalExams = $this->getExamsQuery()->count();
        if (count($this->selectedExams) < $totalExams) {
            $this->selectAll = false; // Uncheck "Select All" if not all are selected
        } elseif (count($this->selectedExams) === $totalExams && $totalExams > 0) {
            $this->selectAll = true;
        }
    }

    public function render()
    {
        $exams = $this->getExamsQuery()->get()->map(function ($exam) {
            return [
                'id' => $exam->id,
                'name' => $exam->name,
                'subject' => $exam->subject->name,
                'class' => $exam->classrooms->pluck('name')->join(', '),
                'date' => $exam->date->format('Y-m-d'),
                'start_time' => $exam->start_time,
                'end_time' => $exam->end_time,
                'duration' => $exam->duration_minutes,
                'status' => (function() use ($exam) {
                    if ($exam->status === 'scheduled') {
                        $now = now();
                        $date = $exam->date->format('Y-m-d');
                        $start = \Carbon\Carbon::parse($date . ' ' . $exam->start_time);
                        $end = \Carbon\Carbon::parse($date . ' ' . $exam->end_time);
                        
                        if ($now->between($start, $end)) {
                            return 'ongoing';
                        } elseif ($now->gt($end)) {
                            return 'completed';
                        }
                    }
                    return $exam->status;
                })(),
                'questions_count' => $exam->questions->count(),
            ];
        });

        return view('teacher.exam.index', [
            'exams' => $exams,
        ])->layout('layouts.teacher')->title('Daftar Ujian');
    }
}
