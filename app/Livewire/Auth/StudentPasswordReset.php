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

    public $isPending = false;

    public function checkStatus()
    {
        $this->validate(['nis' => 'required|string']);

        $student = Student::where('nis', $this->nis)->first();

        if (!$student) {
            $this->errorMessage = 'NIS tidak ditemukan.';
            $this->isPending = false;
            return;
        }

        $user = $student->user;
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
            $this->successMessage = 'Permintaan DISETUJUI! Password Anda telah direset menjadi NIS Anda.';
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
            $this->errorMessage = 'Anda sudah memiliki permintaan reset password yang pending. Mohon tunggu.';
            $this->isPending = true; // Enable polling if matches logic
            return;
        }

        PasswordResetRequest::create([
            'user_id' => $user->id,
            'reason' => $this->reason,
            'status' => 'pending',
        ]);

        $this->successMessage = 'Permintaan reset password berhasil dikirim. Silakan hubungi admin untuk persetujuan.';
        $this->reset(['reason']); // Don't reset NIS so we can keep polling
        $this->isPending = true;
    }

    public function render()
    {
        return view('livewire.auth.student-password-reset')
            ->layout('layouts.guest', ['title' => 'Reset Password']);
    }
}
