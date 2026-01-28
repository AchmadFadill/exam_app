<?php

namespace App\Livewire\Teacher\Exam;

use App\Models\Exam;
use Illuminate\Support\Facades\Auth;
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
        Exam::whereIn('id', $this->selectedExams)->delete();
        
        $this->showBulkDeleteModal = false;
        $this->selectedExams = [];
        $this->selectAll = false;
        $this->dispatch('notify', ['message' => 'Ujian terpilih berhasil dihapus!']);
    }

    public function duplicateExam($id)
    {
        $original = Exam::with(['questions', 'classrooms'])->findOrFail($id);

        $newExam = $original->replicate();
        $newExam->name = 'Salinan - ' . $original->name;
        $newExam->status = 'draft';
        $newExam->date = date('Y-m-d');
        $newExam->token = Exam::generateToken();
        $newExam->save();

        // Copy question relationships
        foreach ($original->questions as $question) {
            $newExam->questions()->attach($question->id, [
                'order' => $question->pivot->order,
                'score' => $question->pivot->score,
            ]);
        }

        // Copy classroom relationships
        $newExam->classrooms()->sync($original->classrooms->pluck('id')->toArray());

        $this->dispatch('notify', ['message' => 'Ujian berhasil diduplikasi!']);
    }

    public function render()
    {
        $user = Auth::user();
        $teacherId = $user->isTeacher() ? $user->teacher->id : null;

        $examsQuery = Exam::with(['subject', 'classrooms', 'questions', 'teacher.user'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc');

        // Filter by teacher if not admin
        if ($teacherId) {
            $examsQuery->where('teacher_id', $teacherId);
        }

        $exams = $examsQuery->get()->map(function ($exam) {
            return [
                'id' => $exam->id,
                'name' => $exam->name,
                'subject' => $exam->subject->name,
                'class' => $exam->classrooms->pluck('name')->join(', '),
                'date' => $exam->date->format('Y-m-d'),
                'start_time' => $exam->start_time,
                'end_time' => $exam->end_time,
                'duration' => $exam->duration_minutes,
                'status' => $exam->status,
                'questions_count' => $exam->questions->count(),
            ];
        });

        return view('teacher.exam.index', [
            'exams' => $exams,
        ])->layout('layouts.teacher')->title('Daftar Ujian');
    }
}
