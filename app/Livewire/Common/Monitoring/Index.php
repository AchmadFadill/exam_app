<?php

namespace App\Livewire\Common\Monitoring;

use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use HasDynamicLayout;

    public function render()
    {
        // Shared logic: In a real app, we would scope this by role/auth()->user()
        // For Admin: Show all exams
        // For Teacher: Show only teacher's exams
        $isAdmin = request()->is('admin/*');

        $activeExams = [
            [
                'id' => 1,
                'name' => 'Ujian Harian Matematika',
                'class' => 'XI IPA 1',
                'subject' => 'Matematika',
                'start_time' => '08:00',
                'end_time' => '09:30',
                'total_students' => 32,
                'working' => 25,
                'finished' => 6,
                'not_started' => 1,
            ],
            // ... more dummy data
        ];

        // If not admin, maybe filter or limit data (Simulated)
        if (!$isAdmin) {
             // Teacher specific logic here
        }

        return $this->applyLayout('livewire.common.monitoring.index', [
            'activeExams' => $activeExams,
            'detailRoute' => $isAdmin ? 'admin.monitor.detail' : 'teacher.monitoring.detail'
        ]);
    }
}
