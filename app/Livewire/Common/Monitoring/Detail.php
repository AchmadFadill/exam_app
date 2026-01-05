<?php

namespace App\Livewire\Common\Monitoring;

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

        // Dummy Student Progress Data
        $students = [
            ['name' => 'Aditya Pratama', 'class' => 'XI IPA 1', 'status' => 'working', 'progress' => '15/30', 'w' => '50%', 'tab_alert' => 0],
            ['name' => 'Bunga Citra', 'class' => 'XI IPA 2', 'status' => 'working', 'progress' => '28/30', 'w' => '93%', 'tab_alert' => 1],
            ['name' => 'Chandra Wijaya', 'class' => 'XI IPA 1', 'status' => 'completed', 'progress' => '30/30', 'w' => '100%', 'tab_alert' => 0],
            // ... more dummy data
        ];

        $live_logs = [
            ['time' => '09:30:10', 'student' => 'Aditya Pratama', 'activity' => 'Menjawab Soal #15', 'type' => 'info'],
            ['time' => '09:28:05', 'student' => 'Eko Kurniawan', 'activity' => 'Keluar Fullscreen', 'type' => 'warning'],
            // ... more dummy data
        ];

        return $this->applyLayout('livewire.common.monitoring.detail', [
            'students' => $students,
            'live_logs' => $live_logs,
            'backRoute' => $isAdmin ? 'admin.monitor' : 'teacher.monitoring'
        ]);
    }

    public function forceSubmit($studentId)
    {
        $this->dispatch('notify', ['message' => 'Ujian siswa berhasil dihentikan (Simulasi)']);
    }
}
