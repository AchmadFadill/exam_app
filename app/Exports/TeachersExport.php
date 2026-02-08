<?php

namespace App\Exports;

use App\Models\Teacher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TeachersExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly ?string $search = null,
        private readonly ?string $subjectId = null,
    ) {
    }

    /**
     * Get the collection to export.
     */
    public function collection()
    {
        return Teacher::with(['user:id,name,email', 'subjects:id,name'])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->subjectId, function ($query) {
                $query->whereHas('subjects', function ($q) {
                    $q->where('subjects.id', $this->subjectId);
                });
            })
            ->orderBy('id')
            ->get();
    }

    /**
     * Map data for each row.
     */
    public function map($teacher): array
    {
        return [
            $teacher->user->name ?? '-',
            $teacher->user->email ?? '-',
            $teacher->subjects->pluck('name')->join(', ') ?: '-',
        ];
    }

    /**
     * Define the headings for the export.
     */
    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'Mata Pelajaran',
        ];
    }
}
