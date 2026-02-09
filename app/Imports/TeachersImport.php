<?php

namespace App\Imports;

use App\Actions\Import\ImportTeacherRowAction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeachersImport implements ToCollection, WithHeadingRow
{
    public int $importedCount = 0;
    public array $errors = [];

    /**
     * Process the imported collection.
     * Expected columns: nama, email, mata_pelajaran (optional - subject code)
     */
    public function collection(Collection $rows)
    {
        $action = app(ImportTeacherRowAction::class);

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Account for header row

            $normalizedRow = is_array($row) ? $row : $row->toArray();
            $result = $action->execute($normalizedRow, $rowNumber);

            if ($result['status'] === 'imported') {
                $this->importedCount++;
            } elseif ($result['status'] === 'error' && isset($result['message'])) {
                $this->errors[] = $result['message'];
            }
        }
    }
}
