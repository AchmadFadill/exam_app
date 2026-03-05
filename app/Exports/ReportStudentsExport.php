<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportStudentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * @param array<int, array<string, mixed>> $students
     */
    public function __construct(
        private readonly array $students,
    ) {}

    public function collection(): Collection
    {
        return collect($this->students);
    }

    public function headings(): array
    {
        return [
            'Nama Siswa',
            'Kelas',
            'Nilai',
            'Status',
        ];
    }

    /**
     * @param array<string, mixed> $student
     */
    public function map($student): array
    {
        return [
            (string) ($student['name'] ?? '-'),
            (string) ($student['class_name'] ?? '-'),
            (string) ($student['score'] ?? '-'),
            (string) ($student['status'] ?? '-'),
        ];
    }
}
