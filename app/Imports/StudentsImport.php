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

    /**
     * Process the imported collection.
     * Expected columns: nis, nama, email, kelas (optional - class name)
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Account for header row
            
            try {
                // Cast all values to strings to handle Excel numeric cells
                $nis = trim((string) ($row['nis'] ?? ''));
                $nama = trim((string) ($row['nama'] ?? ''));
                $email = trim((string) ($row['email'] ?? ''));
                $kelas = trim((string) ($row['kelas'] ?? ''));

                // Skip empty rows
                if (empty($nis) || empty($nama) || empty($email)) {
                    continue;
                }

                // Validate email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[] = "Baris {$rowNumber}: Format email tidak valid.";
                    continue;
                }

                // Check if NIS already exists
                if (Student::where('nis', $nis)->exists()) {
                    $this->errors[] = "Baris {$rowNumber}: NIS {$nis} sudah terdaftar.";
                    continue;
                }

                // Check if email already exists
                if (User::where('email', $email)->exists()) {
                    $this->errors[] = "Baris {$rowNumber}: Email {$email} sudah terdaftar.";
                    continue;
                }

                DB::transaction(function () use ($nis, $nama, $email, $kelas) {
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
                        }
                    }

                    // Create student record
                    Student::create([
                        'user_id' => $user->id,
                        'nis' => $nis,
                        'classroom_id' => $classroomId,
                    ]);

                    $this->importedCount++;
                });
            } catch (\Exception $e) {
                $this->errors[] = "Baris {$rowNumber}: " . $e->getMessage();
            }
        }
    }
}
