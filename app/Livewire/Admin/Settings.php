<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;

class Settings extends Component
{
    use WithFileUploads;

    public $schoolName;
    public $academicYear;
    public $semester;
    public $tabTolerance;
    public $logo; // For file upload

    public function mount()
    {
        // Initialize with default/dummy data
        $this->schoolName = 'SMAIT Baitul Muslim';
        $this->academicYear = '2025/2026';
        $this->semester = 'Ganjil';
        $this->tabTolerance = 3;
    }

    public function save()
    {
        // Validation (Simulated)
        $this->validate([
            'schoolName' => 'required|string|max:255',
            'academicYear' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
            'tabTolerance' => 'required|integer|min:0',
            'logo' => 'nullable|image|max:1024', // 1MB Max
        ]);

        // Logic to save settings would go here (Simulated)
        
        $this->dispatch('notify', ['message' => 'Pengaturan sistem berhasil disimpan!']);
    }

    public function render()
    {
        return view('admin.settings')
            ->extends('layouts.admin')
            ->section('content');
    }
}
