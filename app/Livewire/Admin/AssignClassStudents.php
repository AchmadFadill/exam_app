<?php

namespace App\Livewire\Admin;

use App\Models\Classroom;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class AssignClassStudents extends Component
{
    use WithPagination;

    public Classroom $classroom;
    public string $search = '';
    public bool $selectAll = false;
    public array $selectedStudents = [];

    public function mount(Classroom $classroom): void
    {
        $this->classroom = $classroom;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedStudents = [];
    }

    public function updatedSelectAll(bool $value): void
    {
        if (!$value) {
            $this->selectedStudents = [];
            return;
        }

        $this->selectedStudents = $this->baseQuery()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    public function assignSelected(): void
    {
        if (empty($this->selectedStudents)) {
            $this->dispatch('notify', ['message' => 'Pilih siswa terlebih dahulu.', 'type' => 'error']);
            return;
        }

        $affected = Student::query()
            ->whereIn('id', $this->selectedStudents)
            ->whereNull('classroom_id')
            ->update(['classroom_id' => $this->classroom->id]);

        $this->selectedStudents = [];
        $this->selectAll = false;

        $this->dispatch('notify', ['message' => "{$affected} siswa berhasil ditugaskan ke {$this->classroom->name}."]);
        $this->redirectRoute('admin.classes');
    }

    private function baseQuery()
    {
        return Student::query()
            ->with('user:id,name')
            ->whereNull('classroom_id')
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('nis', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($u) {
                            $u->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->orderBy('nis');
    }

    public function render()
    {
        $students = $this->baseQuery()->paginate(20);

        return view('admin.assign-class-students', [
            'students' => $students,
        ])->layout('layouts.admin', ['title' => 'Assign Siswa']);
    }
}

