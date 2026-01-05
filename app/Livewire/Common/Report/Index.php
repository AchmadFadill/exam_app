<?php

namespace App\Livewire\Common\Report;

use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use HasDynamicLayout;

    public function render()
    {
        $isAdmin = request()->is('admin/*');

        // Dummy Results Data
        $results = [
            [
                'id' => 1,
                'exam_name' => 'Ujian Akhir Semester Matematika',
                'class' => 'XI IPA 1',
                'subject' => 'Matematika',
                'date' => '23 Des 2025',
                'participants' => 32,
                'avg_score' => 82.5
            ],
            [
                'id' => 2,
                'exam_name' => 'Ulangan Harian Fisika - Optik',
                'class' => 'XII IPA 2',
                'subject' => 'Fisika',
                'date' => '20 Des 2025',
                'participants' => 30,
                'avg_score' => 74.2
            ],
        ];

        return $this->applyLayout('livewire.common.report.index', [
            'results' => $results,
            'detailRoute' => $isAdmin ? 'admin.reports.detail' : 'teacher.reports.detail'
        ]);
    }
}
