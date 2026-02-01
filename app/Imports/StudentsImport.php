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
     * Expected columns: nis, nama, email, kelas (optional - class name)
     */
    public function collection(Collection $rows)
    {
        $this->totalRows = $rows->count();
        \Log::info("Starting import of {$this->totalRows} rows");

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Account for header row
            
            try {
                // Cast all values to strings to handle Excel numeric cells
                $nis = trim((string) ($row['nis'] ?? ''));
                $nama = trim((string) ($row['nama'] ?? ''));
                $email = trim((string) ($row['email'] ?? ''));
                $kelas = trim((string) ($row['kelas'] ?? ''));

                // Check for completely empty row
                if (empty($nis) && empty($nama) && empty($email) && empty($kelas)) {
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

                DB::transaction(function () use ($nis, $nama, $email, $kelas, $rowNumber) {
                    // Create user account
                    $user = User::create([
                        'name' => $nama,
                        'email' => $email,
                        'password' => Hash::make($nis), // Default password = NIS
                        'role' => 'student',
                    ]);

                    // Find classroom by name if provided
                    $classroomId = null;
                    if (!empty($kelas)) {
                        $classroom = Classroom::where('name', $kelas)->first();
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
}
