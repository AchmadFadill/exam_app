<?php

namespace App\Livewire\Common\Report;

use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Detail extends Component
{
    use HasDynamicLayout;

    public $examId;
    public $sortBy = 'default'; // default, highest, lowest, fastest, slowest

    public function mount($id)
    {
        $this->examId = $id;
    }

    public function sortByHighest()
    {
        $this->sortBy = 'highest';
    }

    public function sortByLowest()
    {
        $this->sortBy = 'lowest';
    }

    public function sortByFastest()
    {
        $this->sortBy = 'fastest';
    }

    public function sortBySlowest()
    {
        $this->sortBy = 'slowest';
    }

    private function calculateCompletionMinutes($startTime, $endTime)
    {
        // Convert time strings like "08:00" to minutes
        $start = explode(':', $startTime);
        $end = explode(':', $endTime);
        
        $startMinutes = ($start[0] * 60) + $start[1];
        $endMinutes = ($end[0] * 60) + $end[1];
        
        return $endMinutes - $startMinutes;
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
            ['name' => 'Citra Dewi', 'score' => 92, 'status' => 'Lulus', 'started_at' => '08:02', 'submitted_at' => '08:50'],
            ['name' => 'Doni Prasetyo', 'score' => 78, 'status' => 'Lulus', 'started_at' => '08:10', 'submitted_at' => '09:15'],
            ['name' => 'Eka Putri', 'score' => 65, 'status' => 'Tidak Lulus', 'started_at' => '08:00', 'submitted_at' => '09:20'],
        ];

        // Apply sorting
        if ($this->sortBy === 'highest') {
            usort($students, fn($a, $b) => $b['score'] <=> $a['score']);
        } elseif ($this->sortBy === 'lowest') {
            usort($students, fn($a, $b) => $a['score'] <=> $b['score']);
        } elseif ($this->sortBy === 'fastest') {
            usort($students, function($a, $b) {
                $timeA = $this->calculateCompletionMinutes($a['started_at'], $a['submitted_at']);
                $timeB = $this->calculateCompletionMinutes($b['started_at'], $b['submitted_at']);
                return $timeA <=> $timeB;
            });
        } elseif ($this->sortBy === 'slowest') {
            usort($students, function($a, $b) {
                $timeA = $this->calculateCompletionMinutes($a['started_at'], $a['submitted_at']);
                $timeB = $this->calculateCompletionMinutes($b['started_at'], $b['submitted_at']);
                return $timeB <=> $timeA;
            });
        }

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
