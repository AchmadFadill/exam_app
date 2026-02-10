<?php

namespace App\Actions\Import;

use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportTeacherRowAction
{
    /**
     * @param  array<string, mixed>  $row
     * @return array{status: 'imported'|'skipped'|'error', message?: string}
     */
    public function execute(array $row, int $rowNumber): array
    {
        try {
            $nama = trim((string) ($row['nama'] ?? $row['name'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            $mataPelajaran = trim((string) (
                $row['mata_pelajaran']
                ?? $row['subject']
                ?? $row['subject_code']
                ?? ''
            ));

            if ($nama === '' || $email === '') {
                return ['status' => 'error', 'message' => "Baris {$rowNumber}: Kolom nama/email wajib diisi dan header harus sesuai template."];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['status' => 'error', 'message' => "Baris {$rowNumber}: Format email tidak valid."];
            }

            if (User::where('email', $email)->exists()) {
                return ['status' => 'error', 'message' => "Baris {$rowNumber}: Email {$email} sudah terdaftar."];
            }

            DB::transaction(function () use ($nama, $email, $mataPelajaran): void {
                $user = User::create([
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make('12345678'),
                    'role' => 'teacher',
                ]);

                $teacher = Teacher::create([
                    'user_id' => $user->id,
                ]);

                if ($mataPelajaran !== '') {
                    $subject = Subject::where('code', strtoupper($mataPelajaran))->first();
                    if ($subject) {
                        $teacher->subjects()->sync([$subject->id]);
                    }
                }
            });

            return ['status' => 'imported'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => "Baris {$rowNumber}: " . $e->getMessage()];
        }
    }
}
