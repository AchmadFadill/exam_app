<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Component;
use Livewire\WithFileUploads;

class Settings extends Component
{
    use WithFileUploads;

    public $schoolName;
    public $academicYear;
    public $semester;
    public $logo;
    public $existingLogo;

    public function mount()
    {
        $this->schoolName = Setting::getValue('school_name', 'Sekolah CBT');
        $this->academicYear = Setting::getValue('academic_year', date('Y') . '/' . (date('Y') + 1));
        $this->semester = Setting::getValue('semester', 'Ganjil');
        $this->existingLogo = Setting::getValue('school_logo');
    }

    public function save()
    {
        $this->validate([
            'schoolName' => 'required|string|max:255',
            'academicYear' => 'required|string',
            'semester' => 'required|in:Ganjil,Genap',
            'logo' => 'nullable|image|max:2048', // 2MB Max
        ]);

        Setting::setValue('school_name', $this->schoolName);
        Setting::setValue('academic_year', $this->academicYear);
        Setting::setValue('semester', $this->semester);

        if ($this->logo) {
            $path = $this->logo->store('settings', 'public');
            Setting::setValue('school_logo', $path);
            $this->existingLogo = $path;
        }

        session()->flash('success', 'Konfigurasi berhasil disimpan.');
        
        // Force refresh to update Sidebar/Layout global variables
        return redirect()->route('admin.settings');
    }

    public function getAcademicYearOptions()
    {
        $currentYear = date('Y');
        $options = [];
        for ($i = -2; $i < 3; $i++) {
            $year = ($currentYear + $i);
            $nextYear = $year + 1;
            $value = "{$year}/{$nextYear}";
            $options[] = ['value' => $value, 'label' => $value];
        }
        return $options;
    }

    public function render()
    {
        return view('admin.settings')->layout('layouts.admin');
    }
}
