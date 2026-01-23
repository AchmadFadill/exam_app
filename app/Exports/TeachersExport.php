<?php

namespace App\Exports;

use App\Models\Teacher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TeachersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Get the collection to export.
     */
    public function collection()
    {
        return Teacher::with(['user:id,name,email', 'subject:id,name'])->get();
    }

    /**
     * Map data for each row.
     */
    public function map($teacher): array
    {
        return [
            $teacher->user->name ?? '-',
            $teacher->user->email ?? '-',
            $teacher->subject->name ?? '-',
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
