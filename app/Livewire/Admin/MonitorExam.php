<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class MonitorExam extends Component
{
    // Simulate real-time polling
    public function render()
    {
        // Dummy Student Progress Data (Replicated from Teacher view for Admin monitoring)
        $students = [
            ['name' => 'Aditya Pratama', 'class' => 'XI IPA 1', 'status' => 'working', 'progress' => '15/30', 'w' => '50%', 'tab_alert' => 0],
            ['name' => 'Bunga Citra', 'class' => 'XI IPA 2', 'status' => 'working', 'progress' => '28/30', 'w' => '93%', 'tab_alert' => 1],
            ['name' => 'Chandra Wijaya', 'class' => 'XI IPA 1', 'status' => 'completed', 'progress' => '30/30', 'w' => '100%', 'tab_alert' => 0],
            ['name' => 'Dewi Sartika', 'class' => 'XI IPS 1', 'status' => 'not_started', 'progress' => '0/30', 'w' => '0%', 'tab_alert' => 0],
            ['name' => 'Eko Kurniawan', 'class' => 'XI IPA 2', 'status' => 'working', 'progress' => '5/30', 'w' => '16%', 'tab_alert' => 4], // Warning
            ['name' => 'Fajar Santoso', 'class' => 'XI IPS 1', 'status' => 'working', 'progress' => '20/30', 'w' => '66%', 'tab_alert' => 0],
             ['name' => 'Gita Gutawa', 'class' => 'XI IPA 1', 'status' => 'working', 'progress' => '10/30', 'w' => '33%', 'tab_alert' => 0],
             ['name' => 'Hadi Sucipto', 'class' => 'XI IPA 2', 'status' => 'completed', 'progress' => '30/30', 'w' => '100%', 'tab_alert' => 0],
             ['name' => 'Indah Permata', 'class' => 'XI IPS 1', 'status' => 'working', 'progress' => '15/30', 'w' => '50%', 'tab_alert' => 2],
        ];

        return view('admin.monitor-exam', [
            'students' => $students
        ])->extends('layouts.admin')->section('content');
    }

    public function forceSubmit($studentId)
    {
        // Logic to force submit (Simulated)
        $this->dispatch('notify', ['message' => 'Ujian siswa berhasil dihentikan (Simulasi)']);
    }
}
