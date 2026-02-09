<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

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

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Account for header row
            
            try {
                // Cast all values to strings to handle Excel numeric cells
                $nis = $this->valueFromRow($row, ['nis', 'no_induk', 'nomor_induk']);
                $nama = $this->valueFromRow($row, ['nama', 'name']);
                $email = $this->valueFromRow($row, ['email', 'e_mail']);
                $kelas = $this->valueFromRow($row, ['kelas', 'class', 'classroom']);
                $password = $this->valueFromRow($row, ['password', 'pass']);

                // Check for completely empty row
                if (empty($nis) && empty($nama) && empty($email) && empty($kelas) && empty($password)) {
                    $this->skippedCount++;
                    \Log::debug("Row {$rowNumber}: Completely empty, skipping");
                    continue;
                }

                // Validate required fields
                if (empty($nis) || empty($nama)) {
                    $this->errors[] = "Baris {$rowNumber}: NIS dan Nama wajib diisi.";
                    \Log::warning("Row {$rowNumber}: Missing required fields - NIS: '{$nis}', Nama: '{$nama}'");
                    continue;
                }

                // Auto-generate email if empty
                if (empty($email)) {
                    $email = $nis . '@student.local';
                    \Log::debug("Row {$rowNumber}: Auto-generated email: {$email}");
                }

                // Validate email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[] = "Baris {$rowNumber}: Format email tidak valid ({$email}).";
                    \Log::warning("Row {$rowNumber}: Invalid email format: {$email}");
                    continue;
                }

                // Check if NIS already exists
                if (Student::where('nis', $nis)->exists()) {
                    $this->errors[] = "Baris {$rowNumber}: NIS {$nis} sudah terdaftar.";
                    \Log::warning("Row {$rowNumber}: Duplicate NIS: {$nis}");
                    continue;
                }

                // Check if email already exists
                if (User::where('email', $email)->exists()) {
                    // If it's a conflict with the auto-generated email, maybe append random string? 
                    // For now just report error to prevent overwriting.
                    $this->errors[] = "Baris {$rowNumber}: Email {$email} sudah terdaftar.";
                    \Log::warning("Row {$rowNumber}: Duplicate email: {$email}");
                    continue;
                }

                DB::transaction(function () use ($nis, $nama, $email, $kelas, $password, $rowNumber) {
                    // Create user account
                    $user = User::create([
                        'name' => $nama,
                        'email' => $email,
                        'password' => Hash::make($password !== '' ? $password : $nis), // Default password = NIS
                        'role' => 'student',
                    ]);

                    // Find classroom by name if provided
                    $classroomId = null;
                    if (!empty($kelas)) {
                        $classroom = $this->resolveClassroom($kelas);
                        if ($classroom) {
                            $classroomId = $classroom->id;
                        } else {
                            \Log::warning("Row {$rowNumber}: Classroom '{$kelas}' not found");
                        }
                    }

                    // Create student record
                    Student::create([
                        'user_id' => $user->id,
                        'nis' => $nis,
                        'classroom_id' => $classroomId,
                    ]);

                    $this->importedCount++;
                    \Log::info("Row {$rowNumber}: Successfully imported - NIS: {$nis}, Name: {$nama}");
                });
            } catch (\Exception $e) {
                $this->errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                \Log::error("Row {$rowNumber}: Exception - " . $e->getMessage(), [
                    'exception' => $e,
                    'row_data' => $row->toArray()
                ]);
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
