<?php

namespace App\Exports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QuestionGroupExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private readonly string $title,
        private readonly ?int $teacherId = null,
    ) {}

    public function collection()
    {
        return Question::query()
            ->with(['subject:id,name', 'options:id,question_id,label,text,is_correct'])
            ->where('title', $this->title)
            ->when($this->teacherId, fn ($q) => $q->where('teacher_id', $this->teacherId))
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Judul Kelompok',
            'Mata Pelajaran',
            'Tipe',
            'Pertanyaan',
            'Opsi A',
            'Opsi B',
            'Opsi C',
            'Opsi D',
            'Opsi E',
            'Jawaban Benar',
            'Pembahasan',
            'Bobot',
        ];
    }

    public function map($question): array
    {
        $optionsByLabel = $question->options->keyBy('label');
        $correctOption = $question->options->firstWhere('is_correct', true);

        return [
            $question->title,
            $question->subject?->name ?? '',
            $question->type,
            $question->text,
            $optionsByLabel->get('A')?->text ?? '',
            $optionsByLabel->get('B')?->text ?? '',
            $optionsByLabel->get('C')?->text ?? '',
            $optionsByLabel->get('D')?->text ?? '',
            $optionsByLabel->get('E')?->text ?? '',
            $correctOption?->label ?? '',
            $question->explanation ?? '',
            $question->score ?? '',
        ];
    }
}

