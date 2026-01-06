<?php

namespace App\Livewire\Admin;

use App\Models\Classroom;
use App\Models\Student;
use Livewire\Component;

class ManageClass extends Component
{
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showAssignModal = false;

    public $classForm = [
        'name' => '',
        'level' => '',
    ];

    public $selectedClass = null;
    public $search = '';
    public $studentSearch = '';
    public $selectedStudents = [];

    protected $rules = [
        'classForm.name' => 'required|string|max:50',
        'classForm.level' => 'required|in:X,XI,XII',
    ];

    protected $messages = [
        'classForm.name.required' => 'Nama kelas wajib diisi.',
        'classForm.level.required' => 'Tingkatan kelas wajib dipilih.',
    ];

    public function openAddModal()
    {
        $this->reset('classForm', 'selectedClass');
        $this->resetValidation();
        $this->showAddModal = true;
    }

    public function openEditModal($classId)
    {
        $class = Classroom::findOrFail($classId);
        $this->classForm = [
            'name' => $class->name,
            'level' => $class->level,
        ];
        $this->selectedClass = $classId;
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function openDeleteModal($classId)
    {
        $this->selectedClass = $classId;
        $this->showDeleteModal = true;
    }

    public function openAssignModal($classId)
    {
        $this->selectedClass = $classId;
        
        // Pre-select students already in this class
        $this->selectedStudents = Student::where('classroom_id', $classId)
            ->pluck('id')
            ->toArray();
        
        $this->studentSearch = '';
        $this->showAssignModal = true;
    }

    public function saveClass()
    {
        $this->validate();

        if ($this->showEditModal && $this->selectedClass) {
            // Update existing class
            $class = Classroom::findOrFail($this->selectedClass);
            $class->update($this->classForm);
            $message = 'Data kelas berhasil diperbarui!';
        } else {
            // Create new class
            Classroom::create($this->classForm);
            $message = 'Kelas baru berhasil ditambahkan!';
        }

        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->reset('classForm', 'selectedClass');
        $this->dispatch('notify', ['message' => $message]);
    }

    public function deleteClass()
    {
        if ($this->selectedClass) {
            $class = Classroom::findOrFail($this->selectedClass);
            
            // Unassign students from this class before deletion
            Student::where('classroom_id', $this->selectedClass)
                ->update(['classroom_id' => null]);
            
            $class->delete();
        }

        $this->showDeleteModal = false;
        $this->reset('selectedClass');
        $this->dispatch('notify', ['message' => 'Data kelas berhasil dihapus!']);
    }

    public function assignStudents()
    {
        if ($this->selectedClass) {
            // First, unassign all students from this class
            Student::where('classroom_id', $this->selectedClass)
                ->update(['classroom_id' => null]);
            
            // Then assign selected students to this class
            if (!empty($this->selectedStudents)) {
                Student::whereIn('id', $this->selectedStudents)
                    ->update(['classroom_id' => $this->selectedClass]);
            }
        }

        $this->showAssignModal = false;
        $this->reset('selectedStudents', 'studentSearch');
        $this->dispatch('notify', ['message' => 'Penempatan siswa berhasil diperbarui!']);
    }

    public function render()
    {
        // Query classes with student count
        $classesQuery = Classroom::withCount('students');
        
        if ($this->search) {
            $classesQuery->where('name', 'like', '%' . $this->search . '%');
        }
        
        $classes = $classesQuery->orderBy('level')->orderBy('name')->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'level' => $c->level,
                'student_count' => $c->students_count,
            ]);

        // Query all students for assignment modal
        $studentsQuery = Student::with('user');
        
        if ($this->studentSearch) {
            $studentsQuery->where(function($q) {
                $q->where('nis', 'like', '%' . $this->studentSearch . '%')
                  ->orWhereHas('user', function($q2) {
                      $q2->where('name', 'like', '%' . $this->studentSearch . '%');
                  });
            });
        }
        
        $allStudents = $studentsQuery->orderBy('nis')->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->user->name,
                'nis' => $s->nis,
            ]);

        return view('admin.manage-class', [
            'classes' => $classes,
            'allStudents' => $allStudents
        ])->extends('layouts.admin')->section('content');
    }
}
