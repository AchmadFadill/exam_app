<?php

namespace App\Livewire\Auth;

use App\Models\PasswordResetRequest;
use App\Models\Student;
use Livewire\Component;

class StudentPasswordReset extends Component
{
    public $nis;
    public $reason;
    public $successMessage;
    public $errorMessage;

    protected $rules = [
        'nis' => 'required|string|exists:students,nis',
        'reason' => 'required|string|min:5|max:255',
    ];

    public function submit()
    {
        $this->validate();

        $student = Student::where('nis', $this->nis)->first();

        if (!$student) {
            $this->errorMessage = 'NIS tidak ditemukan.';
            return;
        }

        $user = $student->user;

        // Check for existing pending request
        $existing = PasswordResetRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            $this->errorMessage = 'Anda sudah memiliki permintaan reset password yang pending.';
            return;
        }

        PasswordResetRequest::create([
            'user_id' => $user->id,
            'reason' => $this->reason,
            'status' => 'pending',
        ]);

        $this->successMessage = 'Permintaan reset password berhasil dikirim. Silakan hubungi admin untuk persetujuan.';
        $this->reset(['nis', 'reason']);
    }

    public function render()
    {
        return view('livewire.auth.student-password-reset')
            ->layout('layouts.guest', ['title' => 'Reset Password']);
    }
}
