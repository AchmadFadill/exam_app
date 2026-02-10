<?php

namespace App\Imports;

use App\Actions\Import\ImportStudentRowAction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public int $importedCount = 0;
    public array $errors = [];
    public int $skippedCount = 0;
    public int $totalRows = 0;

    /**
     * Process the imported collection.
     * Expected columns: nis, nama, email, kelas, password
     * Aliases are supported, e.g.:
     * - nama/name
     * - kelas/class/classroom
     */
    public function collection(Collection $rows)
    {
        $this->totalRows = $rows->count();
        \Log::info("Starting import of {$this->totalRows} rows");
        $action = app(ImportStudentRowAction::class);

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Account for header row

            $normalizedRow = is_array($row) ? $row : $row->toArray();
            $result = $action->execute($normalizedRow, $rowNumber);

            if ($result['status'] === 'imported') {
                $this->importedCount++;
                continue;
            }

            if ($result['status'] === 'skipped') {
                $this->skippedCount++;
                \Log::debug("Row {$rowNumber}: Completely empty, skipping");
                continue;
            }

            if (isset($result['message'])) {
                $this->errors[] = $result['message'];
                \Log::warning("Row {$rowNumber}: {$result['message']}");
            }
        }

        \Log::info("Import completed - Total: {$this->totalRows}, Imported: {$this->importedCount}, Errors: " . count($this->errors) . ", Skipped: {$this->skippedCount}");
    }

    private function valueFromRow($row, array $keys): string
    {
        foreach ($keys as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function resolveClassroom(string $kelas): ?Classroom
    {
        $exact = Classroom::where('name', $kelas)->first();
        if ($exact) {
            return $exact;
        }

        $normalizedInput = $this->normalizeClassName($kelas);
        if ($normalizedInput === '') {
            return null;
        }

        return Classroom::all()->first(function (Classroom $classroom) use ($normalizedInput) {
            return $this->normalizeClassName($classroom->name) === $normalizedInput;
        });
    }

    private function normalizeClassName(string $name): string
    {
        $value = strtoupper(trim($name));

        // Convert common numeric level notation: 10/11/12 -> X/XI/XII
        $value = preg_replace('/^10\b/', 'X', $value);
        $value = preg_replace('/^11\b/', 'XI', $value);
        $value = preg_replace('/^12\b/', 'XII', $value);

        // Normalize separators
        $value = preg_replace('/[^A-Z0-9]+/', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
