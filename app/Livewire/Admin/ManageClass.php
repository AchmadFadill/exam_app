<?php

namespace App\Livewire\Admin;

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

    public function openAddModal()
    {
        $this->reset('classForm');
        $this->showAddModal = true;
    }

    public function openEditModal($classId)
    {
        // Dummy data for editing
        $this->classForm = [
            'name' => 'X IPA ' . $classId,
            'level' => 'X',
        ];
        $this->selectedClass = $classId;
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
        $this->selectedStudents = [];
        $this->studentSearch = '';
        $this->showAssignModal = true;
    }

    public function saveClass()
    {
        // Dummy save logic
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->dispatch('notify', ['message' => 'Data kelas berhasil disimpan!']);
    }

    public function deleteClass()
    {
        // Dummy delete logic
        $this->showDeleteModal = false;
        $this->dispatch('notify', ['message' => 'Data kelas berhasil dihapus!']);
    }

    public function assignStudents()
    {
        // Dummy assign logic
        $this->showAssignModal = false;
        $this->dispatch('notify', ['message' => 'Siswa berhasil ditambahkan ke kelas!']);
    }

    public function render()
    {
        $classes = [
            ['id' => 1, 'name' => 'X IPA 1', 'level' => 'X', 'student_count' => 32],
            ['id' => 2, 'name' => 'X IPA 2', 'level' => 'X', 'student_count' => 30],
            ['id' => 3, 'name' => 'XI IPS 1', 'level' => 'XI', 'student_count' => 28],
            ['id' => 4, 'name' => 'XI IPS 2', 'level' => 'XI', 'student_count' => 25],
            ['id' => 5, 'name' => 'XII IPA 1', 'level' => 'XII', 'student_count' => 35],
        ];

        if ($this->search) {
            $classes = array_filter($classes, function($class) {
                return str_contains(strtolower($class['name']), strtolower($this->search));
            });
        }

        $allStudents = [
            ['id' => 101, 'name' => 'Ahmad Fulan', 'nis' => '10001'],
            ['id' => 102, 'name' => 'Siti Aminah', 'nis' => '10002'],
            ['id' => 103, 'name' => 'Budi Sudarsono', 'nis' => '10003'],
            ['id' => 104, 'name' => 'Dewi Sartika', 'nis' => '10004'],
            ['id' => 105, 'name' => 'Eko Prasetyo', 'nis' => '10005'],
        ];

        if ($this->studentSearch) {
            $allStudents = array_filter($allStudents, function($student) {
                return str_contains(strtolower($student['name']), strtolower($this->studentSearch)) ||
                       str_contains(strtolower($student['nis']), strtolower($this->studentSearch));
            });
        }

        return view('admin.manage-class', [
            'classes' => $classes,
            'allStudents' => $allStudents
        ])->extends('layouts.admin')->section('content');
    }
}
