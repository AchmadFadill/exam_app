<?php

namespace App\Livewire\Admin;

use Livewire\Component;

use Livewire\WithFileUploads;

class ManageTeacher extends Component
{
    use WithFileUploads;
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showResetPasswordModal = false;

    public $teacherForm = [
        'name' => '',
        'email' => '',
        'password' => '',
        'subject' => ''
    ];

    public $selectedTeacher = null;
    public $search = '';

    // Bulk Action States
    public $selectedTeachers = [];
    public $selectAll = false;
    public $showBulkResetPasswordModal = false;
    public $showBulkDeleteModal = false;
    public $showImportModal = false;
    public $importFile;

    public function openAddModal()
    {
        $this->reset('teacherForm');
        $this->showAddModal = true;
    }

    public function openEditModal($teacherId)
    {
        // Dummy data for editing
        $this->teacherForm = [
            'name' => 'Guru Sample ' . $teacherId,
            'email' => 'guru' . $teacherId . '@example.com',
            'subject' => 'Matematika',
            'password' => ''
        ];
        $this->selectedTeacher = $teacherId;
        $this->showEditModal = true;
    }

    public function openDeleteModal($teacherId)
    {
        $this->selectedTeacher = $teacherId;
        $this->showDeleteModal = true;
    }

    public function openResetPasswordModal($teacherId)
    {
        $this->selectedTeacher = $teacherId;
        $this->showResetPasswordModal = true;
    }

    public function saveTeacher()
    {
        // Dummy save logic
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->dispatch('notify', ['message' => 'Data guru berhasil disimpan!']);
    }

    public function deleteTeacher()
    {
        // Dummy delete logic
        $this->showDeleteModal = false;
        $this->dispatch('notify', ['message' => 'Data guru berhasil dihapus!']);
    }

    public function resetPassword()
    {
        // Dummy reset logic
        $this->showResetPasswordModal = false;
        $this->dispatch('notify', ['message' => 'Password guru berhasil direset!']);
    }

    public function openImportModal()
    {
        $this->reset('importFile');
        $this->showImportModal = true;
    }

    public function importTeachers()
    {
        // Dummy import logic
        $this->showImportModal = false;
        $this->dispatch('notify', ['message' => 'Data guru berhasil diimport!']);
    }

    public function exportTeachers()
    {
        // Dummy export logic
        $this->dispatch('notify', ['message' => 'Data guru sedang diexport...']);
    }

    // Bulk Action Methods
    public function openBulkResetPasswordModal()
    {
        if (empty($this->selectedTeachers)) {
            $this->dispatch('notify', ['message' => 'Pilih guru terlebih dahulu!', 'type' => 'error']);
            return;
        }
        $this->showBulkResetPasswordModal = true;
    }

    public function openBulkDeleteModal()
    {
        if (empty($this->selectedTeachers)) {
            $this->dispatch('notify', ['message' => 'Pilih guru terlebih dahulu!', 'type' => 'error']);
            return;
        }
        $this->showBulkDeleteModal = true;
    }

    public function bulkResetPassword()
    {
        // Dummy bulk reset logic
        $this->showBulkResetPasswordModal = false;
        $this->selectedTeachers = [];
        $this->selectAll = false;
        $this->dispatch('notify', ['message' => 'Password guru terpilih berhasil direset massal!']);
    }

    public function bulkDelete()
    {
        // Dummy bulk delete logic
        $this->showBulkDeleteModal = false;
        $this->selectedTeachers = [];
        $this->selectAll = false;
        $this->dispatch('notify', ['message' => 'Data guru terpilih berhasil dihapus massal!', 'type' => 'success']);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedTeachers = [1, 2, 3, 4, 5];
        } else {
            $this->selectedTeachers = [];
        }
    }

    public function render()
    {
        $teachers = [
            ['id' => 1, 'name' => 'Budi Santoso', 'email' => 'budi@example.com', 'subject' => 'Matematika'],
            ['id' => 2, 'name' => 'Ani Wijaya', 'email' => 'ani@example.com', 'subject' => 'Bahasa Inggris'],
            ['id' => 3, 'name' => 'Candra Kirana', 'email' => 'candra@example.com', 'subject' => 'Fisika'],
            ['id' => 4, 'name' => 'Dedi Setiadi', 'email' => 'dedi@example.com', 'subject' => 'Biologi'],
            ['id' => 5, 'name' => 'Eka Putri', 'email' => 'eka@example.com', 'subject' => 'Kimia'],
        ];

        if ($this->search) {
            $teachers = array_filter($teachers, function($teacher) {
                return str_contains(strtolower($teacher['name']), strtolower($this->search)) ||
                       str_contains(strtolower($teacher['email']), strtolower($this->search));
            });
        }

        return view('admin.manage-teacher', [
            'teachers' => $teachers
        ])->extends('layouts.admin')->section('content');
    }
}
