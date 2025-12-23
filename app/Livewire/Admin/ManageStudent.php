<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;

class ManageStudent extends Component
{
    use WithFileUploads;

    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showResetPasswordModal = false;
    public $showImportModal = false;

    public $studentForm = [
        'name' => '',
        'nis' => '',
        'class' => '',
        'email' => '',
        'password' => ''
    ];

    public $importFile;
    public $selectedStudent = null;
    public $search = '';

    public function openAddModal()
    {
        $this->reset('studentForm');
        $this->showAddModal = true;
    }

    public function openEditModal($studentId)
    {
        // Dummy data for editing
        $this->studentForm = [
            'name' => 'Siswa Sample ' . $studentId,
            'nis' => '12345' . $studentId,
            'class' => 'X IPA 1',
            'email' => 'siswa' . $studentId . '@example.com',
            'password' => ''
        ];
        $this->selectedStudent = $studentId;
        $this->showEditModal = true;
    }

    public function openDeleteModal($studentId)
    {
        $this->selectedStudent = $studentId;
        $this->showDeleteModal = true;
    }

    public function openResetPasswordModal($studentId)
    {
        $this->selectedStudent = $studentId;
        $this->showResetPasswordModal = true;
    }

    public function openImportModal()
    {
        $this->reset('importFile');
        $this->showImportModal = true;
    }

    public function saveStudent()
    {
        // Dummy save logic
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->dispatch('notify', ['message' => 'Data siswa berhasil disimpan!']);
    }

    public function deleteStudent()
    {
        // Dummy delete logic
        $this->showDeleteModal = false;
        $this->dispatch('notify', ['message' => 'Data siswa berhasil dihapus!']);
    }

    public function resetPassword()
    {
        // Dummy reset logic
        $this->showResetPasswordModal = false;
        $this->dispatch('notify', ['message' => 'Password siswa berhasil direset!']);
    }

    public function importStudents()
    {
        // Dummy import logic
        $this->showImportModal = false;
        $this->dispatch('notify', ['message' => 'Data siswa berhasil diimport!']);
    }

    public function render()
    {
        $students = [
            ['id' => 1, 'name' => 'Ahmad Fulan', 'nis' => '10001', 'class' => 'X IPA 1', 'email' => 'ahmad@example.com'],
            ['id' => 2, 'name' => 'Siti Aminah', 'nis' => '10002', 'class' => 'XI IPS 2', 'email' => 'siti@example.com'],
            ['id' => 3, 'name' => 'Budi Sudarsono', 'nis' => '10003', 'class' => 'XII IPA 1', 'email' => 'budi.s@example.com'],
            ['id' => 4, 'name' => 'Dewi Sartika', 'nis' => '10004', 'class' => 'X IPA 2', 'email' => 'dewi@example.com'],
            ['id' => 5, 'name' => 'Eko Prasetyo', 'nis' => '10005', 'class' => 'XI IPA 1', 'email' => 'eko@example.com'],
        ];

        if ($this->search) {
            $students = array_filter($students, function($student) {
                return str_contains(strtolower($student['name']), strtolower($this->search)) ||
                       str_contains(strtolower($student['nis']), strtolower($this->search)) ||
                       str_contains(strtolower($student['email']), strtolower($this->search));
            });
        }

        return view('admin.manage-student', [
            'students' => $students
        ])->extends('layouts.admin')->section('content');
    }
}
