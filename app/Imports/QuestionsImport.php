<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsImport implements ToCollection, WithHeadingRow
{
    protected $title;
    public int $importedCount = 0;
    public int $skippedCount = 0;
    public array $errors = [];

    public function __construct($title)
    {
        $this->title = $title;
    }

    public function collection(Collection $rows)
    {
        // Get teacher_id properly
        $user = Auth::user();
        $teacherId = $user->isTeacher()
            ? optional($user->teacher)->id
            : \App\Models\Teacher::firstOrCreate(['user_id' => $user->id], ['nip' => null])->id;

        if (!$teacherId) {
            $this->errors[] = 'Teacher tidak ditemukan untuk user saat ini.';
            return;
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Heading row offset

            // Skip empty rows
            $pertanyaan = $this->valueFromRow($row, ['pertanyaan', 'question']);
            $mataPelajaran = $this->valueFromRow($row, ['mata_pelajaran', 'mapel', 'subject']);
            $tipe = strtolower($this->valueFromRow($row, ['tipe', 'type']) ?: 'multiple_choice');
            $pembahasan = $this->valueFromRow($row, ['pembahasan', 'explanation']);

            if (empty($pertanyaan) || empty($mataPelajaran)) {
                $this->skippedCount++;
                continue;
            }

            // Find subject by code or name
            $subject = Subject::where('code', strtoupper($mataPelajaran))
                ->orWhere('name', 'LIKE', '%' . $mataPelajaran . '%')
                ->first();
            
            if (!$subject) {
                $this->errors[] = "Baris {$rowNumber}: Mata pelajaran '{$mataPelajaran}' tidak ditemukan.";
                continue; // Skip if subject not found
            }

            // Validate type
            if (!in_array($tipe, ['multiple_choice', 'essay'])) {
                $tipe = 'multiple_choice';
            }

            // Create question with title from modal
            $question = Question::create([
                'teacher_id' => $teacherId,
                'subject_id' => $subject->id,
                'title' => $this->title, // Use title from modal
                'type' => $tipe,
                'text' => $pertanyaan,
                'explanation' => $pembahasan,
            ]);

            // For multiple choice, create options
            if ($tipe === 'multiple_choice') {
                $labels = ['A', 'B', 'C', 'D', 'E'];
                $correctAnswer = strtoupper($this->valueFromRow($row, ['jawaban_benar', 'correct_answer']) ?: 'A');
                
                foreach ($labels as $label) {
                    $optionKey = 'opsi_' . strtolower($label);
                    $optionText = $this->valueFromRow($row, [$optionKey, 'option_' . strtolower($label)]);
                    
                    // Skip if option is empty
                    if (empty($optionText)) {
                        continue;
                    }
                    
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'label' => $label,
                        'text' => $optionText,
                        'is_correct' => $label === $correctAnswer,
                    ]);
                }
            }

            $this->importedCount++;
        }
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
}
