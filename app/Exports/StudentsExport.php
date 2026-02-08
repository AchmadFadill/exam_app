<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly ?string $search = null,
        private readonly ?string $classroomId = null,
    ) {
    }

    /**
     * Get the collection to export.
     */
    public function collection()
    {
        return Student::with(['user:id,name,email', 'classroom:id,name'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nis', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($q2) {
                            $q2->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->classroomId, function ($query) {
                $query->where('classroom_id', $this->classroomId);
            })
            ->orderBy('nis')
            ->get();
    }

    /**
     * Map data for each row.
     */
    public function map($student): array
    {
        return [
            $student->nis,
            $student->user->name ?? '-',
            $student->user->email ?? '-',
            $student->classroom->name ?? '-',
        ];
    }

    /**
     * Define the headings for the export.
     */
    public function headings(): array
    {
        return [
            'NIS',
            'Nama',
            'Email',
            'Kelas',
        ];
    }
}
