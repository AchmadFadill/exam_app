<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class ManageTeacher extends Component
{
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
