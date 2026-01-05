<?php

namespace App\Livewire\Common\Report;

use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Detail extends Component
{
    use HasDynamicLayout;

    public $examId;

    public function mount($id)
    {
        $this->examId = $id;
    }

    public function render()
    {
        $isAdmin = request()->is('admin/*');

        // Dummy Detail Data
        $exam = [
            'id' => 1,
            'exam_name' => 'Ujian Akhir Semester Matematika',
            'class' => 'XI IPA 1',
            'subject' => 'Matematika',
            'date' => '23 Des 2025',
            'avg_score' => 82.5,
            'highest' => 98,
            'lowest' => 65,
            'participants' => 32
        ];

        $students = [
            ['name' => 'Ahmad Fadhil', 'score' => 98, 'status' => 'Lulus', 'started_at' => '08:00', 'submitted_at' => '08:45'],
            ['name' => 'Budi Santoso', 'score' => 85, 'status' => 'Lulus', 'started_at' => '08:05', 'submitted_at' => '09:00'],
            // ... more dummy data
        ];

        $most_failed_questions = [
            [
                'number' => 12,
                'text' => 'Berapakah hasil dari integral sin(x) dx?',
                'failed_count' => 15,
                'failed_percentage' => 46,
                'correct_answer' => '-cos(x) + C'
            ],
            // ... more dummy data
        ];

        return $this->applyLayout('livewire.common.report.detail', [
            'exam' => $exam,
            'students' => $students,
            'most_failed_questions' => $most_failed_questions,
            'backRoute' => $isAdmin ? 'admin.reports.index' : 'teacher.reports.index'
        ]);
    }
}
