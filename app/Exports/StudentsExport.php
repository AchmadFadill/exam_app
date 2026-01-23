<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Get the collection to export.
     */
    public function collection()
    {
        return Student::with(['user:id,name,email', 'classroom:id,name'])->get();
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
