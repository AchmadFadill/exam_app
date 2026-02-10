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
            $nis = $this->valueFromRow($row, ['nis', 'no_induk', 'student_id']);
            $nama = $this->valueFromRow($row, ['nama', 'name']);
            $email = $this->valueFromRow($row, ['email']);
            $kelas = $this->valueFromRow($row, ['kelas', 'class', 'classroom']);
            $password = $this->valueFromRow($row, ['password', 'pass']);

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

            $student = Student::with('user')->where('nis', $nis)->first();

            // Email can be reused only by the same student's user when updating.
            $emailOwner = User::where('email', $email)->first();
            if ($emailOwner && (!$student || $emailOwner->id !== $student->user_id)) {
                return ['status' => 'error', 'message' => "Baris {$rowNumber}: Email {$email} sudah terdaftar."];
            }

            if ($password !== '' && mb_strlen($password) < 8) {
                return ['status' => 'error', 'message' => "Baris {$rowNumber}: Password minimal 8 karakter."];
            }

            DB::transaction(function () use ($student, $nis, $nama, $email, $kelas, $password): void {
                $classroomId = $this->resolveClassroomId($kelas);

                if ($student) {
                    $user = $student->user;
                    $user->name = $nama;
                    $user->email = $email;
                    if ($password !== '') {
                        $user->password = Hash::make($password);
                    }
                    $user->save();

                    $student->update([
                        'classroom_id' => $classroomId,
                    ]);

                    return;
                }

                $user = User::create([
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make($password !== '' ? $password : $nis),
                    'role' => 'student',
                ]);

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

    /**
     * @param array<string, mixed> $row
     * @param array<int, string> $keys
     */
    private function valueFromRow(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function resolveClassroomId(string $kelas): ?int
    {
        if ($kelas === '') {
            return null;
        }

        $exact = Classroom::where('name', $kelas)->first();
        if ($exact) {
            return $exact->id;
        }

        $normalizedInput = $this->normalizeClassName($kelas);
        if ($normalizedInput === '') {
            return null;
        }

        $classroom = Classroom::all()->first(function (Classroom $row) use ($normalizedInput) {
            return $this->normalizeClassName($row->name) === $normalizedInput;
        });

        return $classroom?->id;
    }

    private function normalizeClassName(string $name): string
    {
        $value = strtoupper(trim($name));
        $value = preg_replace('/^10\b/', 'X', $value);
        $value = preg_replace('/^11\b/', 'XI', $value);
        $value = preg_replace('/^12\b/', 'XII', $value);
        $value = preg_replace('/[^A-Z0-9]+/', ' ', $value);

        return trim((string) preg_replace('/\s+/', ' ', $value));
    }
}
