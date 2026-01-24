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
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip empty rows
            if (empty($row['pertanyaan']) || empty($row['mata_pelajaran'])) {
                continue;
            }

            // Cast all data to string to avoid type errors
            $pertanyaan = (string) ($row['pertanyaan'] ?? '');
            $mataPelajaran = (string) ($row['mata_pelajaran'] ?? '');
            $tipe = strtolower((string) ($row['tipe'] ?? 'multiple_choice'));
            $pembahasan = (string) ($row['pembahasan'] ?? '');
            
            // Find subject by name
            $subject = Subject::where('name', 'LIKE', '%' . $mataPelajaran . '%')->first();
            
            if (!$subject) {
                continue; // Skip if subject not found
            }

            // Validate type
            if (!in_array($tipe, ['multiple_choice', 'essay'])) {
                $tipe = 'multiple_choice';
            }

            // Create question
            $question = Question::create([
                'teacher_id' => Auth::id(),
                'subject_id' => $subject->id,
                'type' => $tipe,
                'text' => $pertanyaan,
                'explanation' => $pembahasan,
            ]);

            // For multiple choice, create options
            if ($tipe === 'multiple_choice') {
                $labels = ['A', 'B', 'C', 'D', 'E'];
                $correctAnswer = strtoupper((string) ($row['jawaban_benar'] ?? 'A'));
                
                foreach ($labels as $label) {
                    $optionKey = 'opsi_' . strtolower($label);
                    $optionText = (string) ($row[$optionKey] ?? '');
                    
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
        }
    }
}
