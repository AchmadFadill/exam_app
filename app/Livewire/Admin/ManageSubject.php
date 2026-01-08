<?php

namespace App\Livewire\Admin;

use App\Models\Subject;
use App\Models\Teacher;
use Livewire\Component;

class ManageSubject extends Component
{
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showAssignModal = false;

    public $subjectForm = [
        'name' => '',
        'code' => '',
    ];

    public $selectedSubject = null;
    public $search = '';
    public $teacherSearch = '';
    public $selectedTeacher = null;

    protected $rules = [
        'subjectForm.name' => 'required|string|max:100',
        'subjectForm.code' => 'required|string|max:10',
    ];

    protected $messages = [
        'subjectForm.name.required' => 'Nama mata pelajaran wajib diisi.',
        'subjectForm.code.required' => 'Kode mapel wajib diisi.',
    ];

    public function openAddModal()
    {
        $this->reset('subjectForm', 'selectedSubject');
        $this->resetValidation();
        $this->showAddModal = true;
    }

    public function openEditModal($subjectId)
    {
        $subject = Subject::findOrFail($subjectId);
        $this->subjectForm = [
            'name' => $subject->name,
            'code' => $subject->code,
        ];
        $this->selectedSubject = $subjectId;
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function openDeleteModal($subjectId)
    {
        $this->selectedSubject = $subjectId;
        $this->showDeleteModal = true;
    }

    public function openAssignModal($subjectId)
    {
        $this->selectedSubject = $subjectId;
        
        // Pre-select teacher already assigned to this subject
        $currentTeacher = Teacher::where('subject_id', $subjectId)->first();
        $this->selectedTeacher = $currentTeacher?->id;
        
        $this->teacherSearch = '';
        $this->showAssignModal = true;
    }

    public function saveSubject()
    {
        $this->validate();

        // Uppercase the code
        $this->subjectForm['code'] = strtoupper($this->subjectForm['code']);

        if ($this->showEditModal && $this->selectedSubject) {
            // Update existing subject
            $subject = Subject::findOrFail($this->selectedSubject);
            $subject->update($this->subjectForm);
            $message = 'Mata pelajaran berhasil diperbarui!';
        } else {
            // Create new subject
            Subject::create($this->subjectForm);
            $message = 'Mata pelajaran baru berhasil ditambahkan!';
        }

        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->reset('subjectForm', 'selectedSubject');
        $this->dispatch('notify', ['message' => $message]);
    }

    public function deleteSubject()
    {
        if ($this->selectedSubject) {
            $subject = Subject::findOrFail($this->selectedSubject);
            
            // Unassign teachers from this subject before deletion
            Teacher::where('subject_id', $this->selectedSubject)
                ->update(['subject_id' => null]);
            
            $subject->delete();
        }

        $this->showDeleteModal = false;
        $this->reset('selectedSubject');
        $this->dispatch('notify', ['message' => 'Mata pelajaran berhasil dihapus!']);
    }

    public function assignTeacher()
    {
        if ($this->selectedSubject) {
            // First, unassign any teacher currently assigned to this subject
            Teacher::where('subject_id', $this->selectedSubject)
                ->update(['subject_id' => null]);
            
            // Then assign selected teacher to this subject
            if ($this->selectedTeacher) {
                Teacher::where('id', $this->selectedTeacher)
                    ->update(['subject_id' => $this->selectedSubject]);
            }
        }

        $this->showAssignModal = false;
        $this->reset('selectedTeacher', 'teacherSearch');
        $this->dispatch('notify', ['message' => 'Guru pengampu berhasil ditetapkan!']);
    }

    public function render()
    {
        // Query subjects with their assigned teacher (optimized - no N+1)
        $subjectsQuery = Subject::query()
            ->select('subjects.*')
            ->addSelect([
                'teacher_name' => Teacher::select('users.name')
                    ->join('users', 'teachers.user_id', '=', 'users.id')
                    ->whereColumn('teachers.subject_id', 'subjects.id')
                    ->limit(1)
            ]);
        
        if ($this->search) {
            $subjectsQuery->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }
        
        $subjects = $subjectsQuery->orderBy('name')->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code,
                'teacher' => $s->teacher_name ?? '-',
            ]);

        // Query all teachers for assignment modal
        $teachersQuery = Teacher::with('user');
        
        if ($this->teacherSearch) {
            $teachersQuery->whereHas('user', function($q) {
                $q->where('name', 'like', '%' . $this->teacherSearch . '%');
            });
        }
        
        $teachers = $teachersQuery->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->user->name,
                'email' => $t->user->email,
            ]);

        return view('admin.manage-subject', [
            'subjects' => $subjects,
            'teachers' => $teachers
        ])->layout('layouts.admin', ['title' => 'Mata Pelajaran']);
    }
}
