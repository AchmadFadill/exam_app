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
        'subject_ids' => []
    ];

    public $selectedTeacher = null;
    public $search = '';
    public $filterSubject = '';

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
            'teacherForm.subject_ids' => 'nullable|array',
            'teacherForm.subject_ids.*' => 'exists:subjects,id',
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

    public function closeModal()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->reset('teacherForm', 'selectedTeacher');
        $this->resetValidation();
    }

    public function openEditModal($teacherId)
    {
        $teacher = Teacher::with('user', 'subjects')->findOrFail($teacherId);
        $this->teacherForm = [
            'name' => $teacher->user->name,
            'email' => $teacher->user->email,
            'subject_ids' => $teacher->subjects->pluck('id')->toArray(),
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
        $subjectIds = $this->normalizeSubjectIds($this->teacherForm['subject_ids'] ?? []);

        DB::transaction(function () use ($subjectIds) {
            if ($this->showEditModal && $this->selectedTeacher) {
                // Update existing teacher
                $teacher = Teacher::with('user')->findOrFail($this->selectedTeacher);
                $teacher->user->update([
                    'name' => $this->teacherForm['name'],
                    'email' => $this->teacherForm['email'],
                ]);
                $teacher->subjects()->sync($subjectIds);
                $message = 'Data guru berhasil diperbarui!';
            } else {
                // Create new user and teacher
                $user = User::create([
                    'name' => $this->teacherForm['name'],
                    'email' => $this->teacherForm['email'],
                    'password' => Hash::make($this->teacherForm['password']),
                    'role' => 'teacher',
                ]);

                $teacher = Teacher::create([
                    'user_id' => $user->id,
                ]);
                
                $teacher->subjects()->sync($subjectIds);
                
                $message = 'Guru baru berhasil ditambahkan!';
            }

            $this->dispatch('notify', ['message' => $message]);
        });

        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->reset('teacherForm', 'selectedTeacher');
    }

    public function removeSubject(int $subjectId): void
    {
        $current = $this->normalizeSubjectIds($this->teacherForm['subject_ids'] ?? []);

        $this->teacherForm['subject_ids'] = array_values(array_filter(
            $current,
            fn (int $id) => $id !== $subjectId
        ));
    }

    private function normalizeSubjectIds(array $subjectIds): array
    {
        return collect($subjectIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();
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
                'password' => Hash::make('12345678')
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

    public function downloadTemplate()
    {
        $headers = ['nama', 'email', 'mata_pelajaran'];
        $filename = 'template_guru.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $headers;
                public function __construct($headers) { $this->headers = $headers; }
                public function array(): array { return []; }
                public function headings(): array { return $this->headers; }
            },
            $filename
        );
    }

    public function importTeachers()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ], [
            'importFile.required' => 'File wajib dipilih.',
            'importFile.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
            'importFile.max' => 'Ukuran file maksimal 2MB.',
        ]);

        try {
            $import = new \App\Imports\TeachersImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $this->importFile);

            $this->showImportModal = false;
            $this->reset('importFile');

            if (count($import->errors) > 0) {
                $errorMsg = implode(' | ', array_slice($import->errors, 0, 3));
                $this->dispatch('notify', [
                    'message' => "Berhasil import {$import->importedCount} guru. Ada error: {$errorMsg}",
                    'type' => 'warning'
                ]);
            } else {
                $this->dispatch('notify', ['message' => "Berhasil import {$import->importedCount} guru!"]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Gagal import: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function exportTeachers()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\TeachersExport(
                search: $this->search ?: null,
                subjectId: $this->filterSubject ?: null,
            ),
            'data_guru_' . now()->format('Y-m-d') . '.xlsx'
        );
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
                    'password' => Hash::make('12345678')
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
            'subjects:id,name'
        ])->select('id', 'user_id');

        if ($this->search) {
            $teachersQuery->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterSubject) {
            $teachersQuery->whereHas('subjects', function ($q) {
                $q->where('subjects.id', $this->filterSubject);
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
