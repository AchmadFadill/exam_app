<?php

namespace App\Livewire\Admin;

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

    public function openAddModal()
    {
        $this->reset('subjectForm');
        $this->showAddModal = true;
    }

    public function openEditModal($subjectId)
    {
        // Dummy data for editing
        $data = $this->getSubjects();
        $subject = collect($data)->firstWhere('id', $subjectId);
        
        if ($subject) {
            $this->subjectForm = [
                'name' => $subject['name'],
                'code' => $subject['code'],
            ];
            $this->selectedSubject = $subjectId;
            $this->showEditModal = true;
        }
    }

    public function openDeleteModal($subjectId)
    {
        $this->selectedSubject = $subjectId;
        $this->showDeleteModal = true;
    }

    public function openAssignModal($subjectId)
    {
        $this->selectedSubject = $subjectId;
        $this->selectedTeacher = null;
        $this->teacherSearch = '';
        $this->showAssignModal = true;
    }

    public function saveSubject()
    {
        // Dummy save logic
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->dispatch('notify', ['message' => 'Mata pelajaran berhasil disimpan!']);
    }

    public function deleteSubject()
    {
        // Dummy delete logic
        $this->showDeleteModal = false;
        $this->dispatch('notify', ['message' => 'Mata pelajaran berhasil dihapus!']);
    }

    public function assignTeacher()
    {
        // Dummy assign logic
        $this->showAssignModal = false;
        $this->dispatch('notify', ['message' => 'Guru pengampu berhasil ditetapkan!']);
    }

    private function getSubjects()
    {
        return [
            ['id' => 1, 'name' => 'Matematika Wajib', 'code' => 'MTK-W', 'teacher' => 'Budi Santoso'],
            ['id' => 2, 'name' => 'Bahasa Indonesia', 'code' => 'BIND', 'teacher' => 'Siti Aminah'],
            ['id' => 3, 'name' => 'Bahasa Inggris', 'code' => 'BING', 'teacher' => 'John Doe'],
            ['id' => 4, 'name' => 'Fisika', 'code' => 'FIS', 'teacher' => '-'],
            ['id' => 5, 'name' => 'Biologi', 'code' => 'BIO', 'teacher' => 'Dewi Sartika'],
            ['id' => 6, 'name' => 'Kimia', 'code' => 'KIM', 'teacher' => '-'],
        ];
    }

    public function render()
    {
        $subjects = $this->getSubjects();

        if ($this->search) {
            $subjects = array_filter($subjects, function($subject) {
                return str_contains(strtolower($subject['name']), strtolower($this->search)) ||
                       str_contains(strtolower($subject['code']), strtolower($this->search));
            });
        }

        $teachers = [
            ['id' => 1, 'name' => 'Budi Santoso', 'email' => 'budi@example.com'],
            ['id' => 2, 'name' => 'Siti Aminah', 'email' => 'siti@example.com'],
            ['id' => 3, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 4, 'name' => 'Dewi Sartika', 'email' => 'dewi@example.com'],
            ['id' => 5, 'name' => 'Eko Prasetyo', 'email' => 'eko@example.com'],
        ];

        if ($this->teacherSearch) {
            $teachers = array_filter($teachers, function($teacher) {
                return str_contains(strtolower($teacher['name']), strtolower($this->teacherSearch));
            });
        }

        return view('admin.manage-subject', [
            'subjects' => $subjects,
            'teachers' => $teachers
        ])->extends('layouts.admin')->section('content');
    }
}
