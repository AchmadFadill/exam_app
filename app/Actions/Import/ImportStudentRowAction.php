<?php

namespace App\Actions\Import;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportStudentRowAction
{
    /**
     * @param  array<string, mixed>  $row
     * @return array{status: 'imported'|'skipped'|'error', message?: string}
     */
    public function execute(array $row, int $rowNumber): array
    {
        try {
            $nis = trim((string) ($row['nis'] ?? ''));
            $nama = trim((string) ($row['nama'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            $kelas = trim((string) ($row['kelas'] ?? ''));

            if ($nis === '' && $nama === '' && $email === '' && $kelas === '') {
                return ['status' => 'skipped'];
            }

            if ($nis === '' || $nama === '') {
                return ['status' => 'error', 'message' => "Baris {$rowNumber}: NIS dan Nama wajib diisi."];
            }

            if ($email === '') {
                $email = $nis . '@student.local';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['status' => 'error', 'message' => "Baris {$rowNumber}: Format email tidak valid ({$email})."];
            }

            if (Student::where('nis', $nis)->exists()) {
                return ['status' => 'error', 'message' => "Baris {$rowNumber}: NIS {$nis} sudah terdaftar."];
            }

            if (User::where('email', $email)->exists()) {
                return ['status' => 'error', 'message' => "Baris {$rowNumber}: Email {$email} sudah terdaftar."];
            }

            DB::transaction(function () use ($nis, $nama, $email, $kelas): void {
                $user = User::create([
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make($nis),
                    'role' => 'student',
                ]);

                $classroomId = null;
                if ($kelas !== '') {
                    $classroom = Classroom::where('name', $kelas)->first();
                    $classroomId = $classroom?->id;
                }

                Student::create([
                    'user_id' => $user->id,
                    'nis' => $nis,
                    'classroom_id' => $classroomId,
                ]);
            });

            return ['status' => 'imported'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => "Baris {$rowNumber}: " . $e->getMessage()];
        }
    }
}
