<?php

namespace App\Livewire\Auth;

use App\Models\PasswordResetRequest;
use App\Models\Teacher;
use Livewire\Component;

class TeacherPasswordReset extends Component
{
    public $email;
    public $reason;
    public $successMessage;
    public $errorMessage;

    protected $rules = [
        'email' => 'required|email|exists:users,email',
        'reason' => 'required|string|min:5|max:255',
    ];

    public function submit()
    {
        $this->validate();

        $user = \App\Models\User::where('email', $this->email)
            ->where('role', 'teacher')
            ->first();

        if (!$user) {
            $this->errorMessage = 'Email tidak ditemukan atau bukan akun Guru.';
            return;
        }

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
        $this->reset(['email', 'reason']);
    }

    public function render()
    {
        return view('livewire.auth.teacher-password-reset')
            ->layout('layouts.guest', ['title' => 'Reset Password Guru']);
    }
}
