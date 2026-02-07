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

    public $isPending = false;

    public function checkStatus()
    {
        $this->validate(['email' => 'required|email']);

        $user = \App\Models\User::where('email', $this->email)
            ->where('role', 'teacher')
            ->first();

        if (!$user) {
            $this->errorMessage = 'Email tidak ditemukan atau bukan akun Guru.';
            $this->isPending = false;
            return;
        }

        $latestRequest = PasswordResetRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$latestRequest) {
            $this->errorMessage = 'Belum ada permintaan reset password untuk akun ini.';
            $this->isPending = false;
            return;
        }

        if ($latestRequest->status === 'pending') {
            $this->successMessage = 'Permintaan Anda statusnya: MENUNGGU PERSETUJUAN (PENDING).';
            $this->errorMessage = null;
            $this->isPending = true;
        } elseif ($latestRequest->status === 'approved') {
            $this->successMessage = 'Permintaan DISETUJUI! Password Anda telah direset menjadi Email Anda.';
            $this->errorMessage = null;
            $this->isPending = false;
        } elseif ($latestRequest->status === 'rejected') {
            $this->errorMessage = 'Permintaan Anda DITOLAK. Silakan hubungi admin sekolah.';
            $this->successMessage = null;
            $this->isPending = false;
        }
    }

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
            $this->errorMessage = 'Anda sudah memiliki permintaan reset password yang pending. Mohon tunggu.';
            $this->isPending = true;
            return;
        }

        PasswordResetRequest::create([
            'user_id' => $user->id,
            'reason' => $this->reason,
            'status' => 'pending',
        ]);

        $this->successMessage = 'Permintaan reset password berhasil dikirim. Silakan hubungi admin untuk persetujuan.';
        $this->reset(['reason']); // Keep Email
        $this->isPending = true;
    }

    public function render()
    {
        return view('livewire.auth.teacher-password-reset')
            ->layout('layouts.guest', ['title' => 'Reset Password Guru']);
    }
}
