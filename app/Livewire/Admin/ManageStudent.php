<?php

namespace App\Livewire\Admin;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageStudent extends Component
{
    use WithFileUploads, WithPagination;

    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showResetPasswordModal = false;
    public $showImportModal = false;

    public $studentForm = [
        'name' => '',
        'nis' => '',
        'classroom_id' => '',
        'email' => '',
        'password' => ''
    ];

    public $importFile;
    public $selectedStudent = null;
    public $search = '';

    // Bulk Action States
    public $selectedStudents = [];
    public $selectAll = false;
    public $showBulkClassModal = false;
    public $showBulkDeleteModal = false;
    public $bulkClassId = '';

    // Export Filter States
    public $showExportModal = false;
    public $exportClassId = '';

    protected function rules()
    {
        $rules = [
            'studentForm.name' => 'required|string|max:100',
            'studentForm.nis' => 'required|string|max:20',
            'studentForm.classroom_id' => 'nullable|exists:classrooms,id',
            'studentForm.email' => 'nullable|email|max:100',
        ];

        if ($this->showAddModal) {
            $rules['studentForm.nis'] .= '|unique:students,nis';
            $rules['studentForm.password'] = 'required|string|min:8';
            if ($this->studentForm['email']) {
                $rules['studentForm.email'] .= '|unique:users,email';
            }
        } else {
            $nisExclude = $this->selectedStudent ? Student::find($this->selectedStudent)?->nis : null;
            $rules['studentForm.nis'] .= '|unique:students,nis,' . $this->selectedStudent;
            if ($this->studentForm['email']) {
                $rules['studentForm.email'] .= '|unique:users,email,' . $this->getEditingUserId();
            }
        }

        return $rules;
    }

    protected $messages = [
        'studentForm.name.required' => 'Nama siswa wajib diisi.',
        'studentForm.nis.required' => 'NIS wajib diisi.',
        'studentForm.nis.unique' => 'NIS sudah digunakan.',
        'studentForm.email.email' => 'Format email tidak valid.',
        'studentForm.email.unique' => 'Email sudah digunakan.',
        'studentForm.password.required' => 'Password wajib diisi.',
        'studentForm.password.min' => 'Password minimal 8 karakter.',
    ];

    private function getEditingUserId()
    {
        if ($this->selectedStudent) {
            $student = Student::find($this->selectedStudent);
            return $student?->user_id;
        }
        return null;
    }

    public function openAddModal()
    {
        $this->reset('studentForm', 'selectedStudent');
        $this->resetValidation();
        $this->showAddModal = true;
    }

    public function closeModal()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->reset('studentForm', 'selectedStudent');
        $this->resetValidation();
    }

    public function openEditModal($studentId)
    {
        $student = Student::with('user:id,name,email', 'classroom:id,name')
            ->select('id', 'user_id', 'nis', 'classroom_id')
            ->findOrFail($studentId);
            
        $this->studentForm = [
            'name' => $student->user->name,
            'nis' => $student->nis,
            'classroom_id' => $student->classroom_id ?? '',
            'email' => $student->user->email,
            'password' => ''
        ];
        $this->selectedStudent = $studentId;
        $this->resetValidation();
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
        $this->validate();

        DB::transaction(function () {
            if ($this->showEditModal && $this->selectedStudent) {
                // Update existing student
                $student = Student::with('user')->findOrFail($this->selectedStudent);
                $student->user->update([
                    'name' => $this->studentForm['name'],
                    'email' => $this->studentForm['email'] ?: null,
                ]);
                $student->update([
                    'nis' => $this->studentForm['nis'],
                    'classroom_id' => $this->studentForm['classroom_id'] ?: null,
                ]);
                $message = 'Data siswa berhasil diperbarui!';
            } else {
                // Create new user and student
                $user = User::create([
                    'name' => $this->studentForm['name'],
                    'email' => $this->studentForm['email'] ?: $this->studentForm['nis'] . '@student.local',
                    'password' => Hash::make($this->studentForm['password']),
                    'role' => 'student',
                ]);

                Student::create([
                    'user_id' => $user->id,
                    'nis' => $this->studentForm['nis'],
                    'classroom_id' => $this->studentForm['classroom_id'] ?: null,
                ]);
                $message = 'Siswa baru berhasil ditambahkan!';
            }

            $this->dispatch('notify', ['message' => $message]);
        });

        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->reset('studentForm', 'selectedStudent');
    }

    public function deleteStudent()
    {
        if ($this->selectedStudent) {
            DB::transaction(function () {
                $student = Student::with('user')->findOrFail($this->selectedStudent);
                $userId = $student->user_id;
                $student->delete();
                User::destroy($userId);
            });
        }

        $this->showDeleteModal = false;
        $this->reset('selectedStudent');
        $this->dispatch('notify', ['message' => 'Data siswa berhasil dihapus!']);
    }

    public function resetPassword()
    {
        if ($this->selectedStudent) {
            $student = Student::with('user')->findOrFail($this->selectedStudent);
            // Reset password to NIS
            $student->user->update([
                'password' => Hash::make($student->nis)
            ]);
        }

        $this->showResetPasswordModal = false;
        $this->reset('selectedStudent');
        $this->dispatch('notify', ['message' => 'Password siswa berhasil direset ke NIS!']);
    }

    public function importStudents()
    {
        // TODO: Implement Excel import
        $this->showImportModal = false;
        $this->dispatch('notify', ['message' => 'Fitur import akan segera tersedia!']);
    }

    public function exportStudents()
    {
        $this->reset(['exportClassId']);
        $this->showExportModal = true;
    }

    public function processExport()
    {
        // TODO: Implement Excel export
        $this->showExportModal = false;
        $this->dispatch('notify', ['message' => 'Fitur export akan segera tersedia!']);
    }

    // Bulk Action Methods
    public function openBulkClassModal()
    {
        if (empty($this->selectedStudents)) {
            $this->dispatch('notify', ['message' => 'Pilih siswa terlebih dahulu!', 'type' => 'error']);
            return;
        }
        $this->bulkClassId = '';
        $this->showBulkClassModal = true;
    }

    public function openBulkDeleteModal()
    {
        if (empty($this->selectedStudents)) {
            $this->dispatch('notify', ['message' => 'Pilih siswa terlebih dahulu!', 'type' => 'error']);
            return;
        }
        $this->showBulkDeleteModal = true;
    }

    public function saveBulkClass()
    {
        if ($this->bulkClassId) {
            Student::whereIn('id', $this->selectedStudents)
                ->update(['classroom_id' => $this->bulkClassId]);
        }

        $this->showBulkClassModal = false;
        $count = count($this->selectedStudents);
        $this->selectedStudents = [];
        $this->selectAll = false;
        $this->dispatch('notify', ['message' => "Kelas {$count} siswa berhasil diubah!"]);
    }

    public function bulkDelete()
    {
        DB::transaction(function () {
            $students = Student::with('user')->whereIn('id', $this->selectedStudents)->get();
            $userIds = $students->pluck('user_id')->toArray();
            Student::whereIn('id', $this->selectedStudents)->delete();
            User::whereIn('id', $userIds)->delete();
        });

        $this->showBulkDeleteModal = false;
        $count = count($this->selectedStudents);
        $this->selectedStudents = [];
        $this->selectAll = false;
        $this->dispatch('notify', ['message' => "{$count} siswa berhasil dihapus massal!"]);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Select only IDs from current page for better UX
            $this->selectedStudents = Student::pluck('id')->toArray();
        } else {
            $this->selectedStudents = [];
        }
    }

    public function render()
    {
        // Optimized query with eager loading and select
        $studentsQuery = Student::with([
            'user:id,name,email',
            'classroom:id,name'
        ])->select('id', 'user_id', 'nis', 'classroom_id');

        if ($this->search) {
            $studentsQuery->where(function($q) {
                $q->where('nis', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($q2) {
                      $q2->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Use pagination for large datasets
        $students = $studentsQuery->orderBy('nis')->paginate(15);

        // Get classrooms for dropdown (small dataset, no pagination needed)
        $classrooms = Classroom::select('id', 'name', 'level')
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return view('admin.manage-student', [
            'students' => $students,
            'classrooms' => $classrooms,
        ])->layout('layouts.admin', ['title' => 'Data Siswa']);
    }
}
