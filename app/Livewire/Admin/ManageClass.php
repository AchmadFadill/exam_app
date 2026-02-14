<?php

namespace App\Livewire\Admin;

use App\Models\Classroom;
use Livewire\Component;

class ManageClass extends Component
{
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;

    public $classForm = [
        'name' => '',
        'level' => '',
        'teacher_id' => '',
    ];

    public $selectedClass = null;
    public $search = '';

    protected function rules()
    {
        return [
            'classForm.name' => 'required|string|max:50',
            'classForm.level' => 'required|in:X,XI,XII',
            'classForm.teacher_id' => 'nullable|exists:teachers,id|unique:classrooms,teacher_id,' . $this->selectedClass,
        ];
    }

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

    public function closeModal()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->resetValidation();
    }

    public function openEditModal($classId)
    {
        $class = Classroom::findOrFail($classId);
        $this->classForm = [
            'name' => $class->name,
            'level' => $class->level,
            'teacher_id' => $class->teacher_id ?? '',
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

    public function saveClass()
    {
        $this->validate();
        $data = $this->classForm;
        $data['teacher_id'] = !empty($data['teacher_id']) ? (int) $data['teacher_id'] : null;

        if ($this->showEditModal && $this->selectedClass) {
            // Update existing class
            $class = Classroom::findOrFail($this->selectedClass);
            $class->update($data);
            $message = 'Data kelas berhasil diperbarui!';
        } else {
            // Create new class
            Classroom::create($data);
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

    public function getAvailableTeachersProperty()
    {
        return \App\Models\Teacher::with('user')
            ->whereDoesntHave('classroom')
            ->when($this->selectedClass, function ($query) {
                $query->orWhereHas('classroom', function ($q) {
                    $q->where('id', $this->selectedClass);
                });
            })
            ->get();
    }

    public function render()
    {
        // Query classes with student count
        $classesQuery = Classroom::withCount('students');
        
        if ($this->search) {
            $classesQuery->where('name', 'like', '%' . $this->search . '%');
        }
        
        $classes = $classesQuery->with('teacher.user')->orderBy('level')->orderBy('name')->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'level' => $c->level,
                'student_count' => $c->students_count,
                'teacher_name' => $c->teacher ? $c->teacher->user->name : '-',
            ]);

        return view('admin.manage-class', [
            'classes' => $classes,
            'teachers' => $this->availableTeachers,
        ])->layout('layouts.admin', ['title' => 'Data Kelas']);
    }
}
