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
    public $logo; // For file upload

    public function mount()
    {
        // Initialize with default/dummy data
        $this->schoolName = 'SMAIT Baitul Muslim';
        $this->academicYear = '2025/2026';
        $this->semester = 'Ganjil';
    }

    public function save()
    {
        // Validation (Simulated)
        $this->validate([
            'schoolName' => 'required|string|max:255',
            'academicYear' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
            'logo' => 'nullable|image|max:1024', // 1MB Max
        ]);

        // Logic to save settings would go here (Simulated)
        
        $this->dispatch('notify', ['message' => 'Pengaturan sistem berhasil disimpan!']);
    }

    public function getAcademicYearOptions()
    {
        $currentYear = date('Y');
        $years = [];
        
        // Generate academic years from current year to 5 years in the future
        for ($i = 0; $i <= 5; $i++) {
            $startYear = $currentYear + $i;
            $endYear = $startYear + 1;
            $yearStr = "$startYear/$endYear";
            $years[] = [
                'value' => $yearStr,
                'label' => $yearStr
            ];
        }
        
        return $years;
    }

    public function render()
    {
        return view('admin.settings')
            ->layout('layouts.admin', ['title' => 'Pengaturan Sistem']);
    }
}
