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
     * Expected columns: nis, nama, email, kelas (optional - class name)
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
}
