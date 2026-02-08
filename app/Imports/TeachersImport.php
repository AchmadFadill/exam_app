<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

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
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Account for header row
            
            try {
                // Cast all values to strings to handle Excel numeric cells
                $nama = trim((string) ($row['nama'] ?? ''));
                $email = trim((string) ($row['email'] ?? ''));
                $mataPelajaran = trim((string) ($row['mata_pelajaran'] ?? ''));

                // Skip empty rows
                if (empty($nama) || empty($email)) {
                    continue;
                }

                // Validate email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[] = "Baris {$rowNumber}: Format email tidak valid.";
                    continue;
                }

                // Check if email already exists
                if (User::where('email', $email)->exists()) {
                    $this->errors[] = "Baris {$rowNumber}: Email {$email} sudah terdaftar.";
                    continue;
                }

                DB::transaction(function () use ($nama, $email, $mataPelajaran) {
                    // Create user account
                    $user = User::create([
                        'name' => $nama,
                        'email' => $email,
                        'password' => Hash::make('12345678'), // Default password
                        'role' => 'teacher',
                    ]);

                    // Create teacher record
                    $teacher = Teacher::create([
                        'user_id' => $user->id,
                    ]);

                    // Find subject by code if provided and attach through pivot
                    if (!empty($mataPelajaran)) {
                        $subject = Subject::where('code', strtoupper($mataPelajaran))->first();
                        if ($subject) {
                            $teacher->subjects()->sync([$subject->id]);
                        }
                    }

                    $this->importedCount++;
                });
            } catch (\Exception $e) {
                $this->errors[] = "Baris {$rowNumber}: " . $e->getMessage();
            }
        }
    }
}
