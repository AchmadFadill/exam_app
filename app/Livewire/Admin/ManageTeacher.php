<?php

namespace App\Livewire\Admin;

use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        'subject_id' => ''
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

    protected function rules()
    {
        $rules = [
            'teacherForm.name' => 'required|string|max:100',
            'teacherForm.email' => 'required|email|max:100',
            'teacherForm.subject_id' => 'nullable|exists:subjects,id',
        ];

        if ($this->showAddModal) {
            $rules['teacherForm.email'] .= '|unique:users,email';
            $rules['teacherForm.password'] = 'required|string|min:8';
        } else {
            $rules['teacherForm.email'] .= '|unique:users,email,' . $this->getEditingUserId();
        }

        return $rules;
    }

    protected $messages = [
        'teacherForm.name.required' => 'Nama guru wajib diisi.',
        'teacherForm.email.required' => 'Email wajib diisi.',
        'teacherForm.email.email' => 'Format email tidak valid.',
        'teacherForm.email.unique' => 'Email sudah digunakan.',
        'teacherForm.password.required' => 'Password wajib diisi.',
        'teacherForm.password.min' => 'Password minimal 8 karakter.',
    ];

    private function getEditingUserId()
    {
        if ($this->selectedTeacher) {
            $teacher = Teacher::find($this->selectedTeacher);
            return $teacher?->user_id;
        }
        return null;
    }

    public function openAddModal()
    {
        $this->reset('teacherForm', 'selectedTeacher');
        $this->resetValidation();
        $this->showAddModal = true;
    }

    public function openEditModal($teacherId)
    {
        $teacher = Teacher::with('user', 'subject')->findOrFail($teacherId);
        $this->teacherForm = [
            'name' => $teacher->user->name,
            'email' => $teacher->user->email,
            'subject_id' => $teacher->subject_id ?? '',
            'password' => ''
        ];
        $this->selectedTeacher = $teacherId;
        $this->resetValidation();
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
        $this->validate();

        DB::transaction(function () {
            if ($this->showEditModal && $this->selectedTeacher) {
                // Update existing teacher
                $teacher = Teacher::with('user')->findOrFail($this->selectedTeacher);
                $teacher->user->update([
                    'name' => $this->teacherForm['name'],
                    'email' => $this->teacherForm['email'],
                ]);
                $teacher->update([
                    'subject_id' => $this->teacherForm['subject_id'] ?: null,
                ]);
                $message = 'Data guru berhasil diperbarui!';
            } else {
                // Create new user and teacher
                $user = User::create([
                    'name' => $this->teacherForm['name'],
                    'email' => $this->teacherForm['email'],
                    'password' => Hash::make($this->teacherForm['password']),
                    'role' => 'teacher',
                ]);

                Teacher::create([
                    'user_id' => $user->id,
                    'subject_id' => $this->teacherForm['subject_id'] ?: null,
                ]);
                $message = 'Guru baru berhasil ditambahkan!';
            }

            $this->dispatch('notify', ['message' => $message]);
        });

        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->reset('teacherForm', 'selectedTeacher');
    }

    public function deleteTeacher()
    {
        if ($this->selectedTeacher) {
            DB::transaction(function () {
                $teacher = Teacher::with('user')->findOrFail($this->selectedTeacher);
                $userId = $teacher->user_id;
                $teacher->delete();
                User::destroy($userId);
            });
        }

        $this->showDeleteModal = false;
        $this->reset('selectedTeacher');
        $this->dispatch('notify', ['message' => 'Data guru berhasil dihapus!']);
    }

    public function resetPassword()
    {
        if ($this->selectedTeacher) {
            $teacher = Teacher::with('user')->findOrFail($this->selectedTeacher);
            $teacher->user->update([
                'password' => Hash::make('password')
            ]);
        }

        $this->showResetPasswordModal = false;
        $this->reset('selectedTeacher');
        $this->dispatch('notify', ['message' => 'Password guru berhasil direset ke default!']);
    }

    public function openImportModal()
    {
        $this->reset('importFile');
        $this->showImportModal = true;
    }

    public function importTeachers()
    {
        // TODO: Implement Excel import
        $this->showImportModal = false;
        $this->dispatch('notify', ['message' => 'Fitur import akan segera tersedia!']);
    }

    public function exportTeachers()
    {
        // TODO: Implement Excel export
        $this->dispatch('notify', ['message' => 'Fitur export akan segera tersedia!']);
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
        DB::transaction(function () {
            $teachers = Teacher::with('user')->whereIn('id', $this->selectedTeachers)->get();
            foreach ($teachers as $teacher) {
                $teacher->user->update([
                    'password' => Hash::make('password')
                ]);
            }
        });

        $this->showBulkResetPasswordModal = false;
        $count = count($this->selectedTeachers);
        $this->selectedTeachers = [];
        $this->selectAll = false;
        $this->dispatch('notify', ['message' => "Password {$count} guru berhasil direset massal!"]);
    }

    public function bulkDelete()
    {
        DB::transaction(function () {
            $teachers = Teacher::with('user')->whereIn('id', $this->selectedTeachers)->get();
            $userIds = $teachers->pluck('user_id')->toArray();
            Teacher::whereIn('id', $this->selectedTeachers)->delete();
            User::whereIn('id', $userIds)->delete();
        });

        $this->showBulkDeleteModal = false;
        $count = count($this->selectedTeachers);
        $this->selectedTeachers = [];
        $this->selectAll = false;
        $this->dispatch('notify', ['message' => "{$count} guru berhasil dihapus massal!"]);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedTeachers = Teacher::pluck('id')->toArray();
        } else {
            $this->selectedTeachers = [];
        }
    }

    public function render()
    {
        // Query teachers with relationships - optimized with select
        $teachersQuery = Teacher::with([
            'user:id,name,email',
            'subject:id,name'
        ])->select('id', 'user_id', 'subject_id');

        if ($this->search) {
            $teachersQuery->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Use pagination for large datasets
        $teachers = $teachersQuery->paginate(15);

        // Get all subjects for dropdown (typically small dataset)
        $subjects = Subject::select('id', 'name')->orderBy('name')->get();

        return view('admin.manage-teacher', [
            'teachers' => $teachers,
            'subjects' => $subjects,
        ])->layout('layouts.admin', ['title' => 'Data Guru']);
    }
}
