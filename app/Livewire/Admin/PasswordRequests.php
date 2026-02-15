<?php

namespace App\Livewire\Admin;

use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class PasswordRequests extends Component
{
    public $selected = [];
    public $selectAll = false;

    // Toggle Select All
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = PasswordResetRequest::where('status', 'pending')
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    // Bulk Approve
    public function bulkApprove()
    {
        $count = 0;
        
        \Illuminate\Support\Facades\DB::transaction(function () use (&$count) {
            $requests = PasswordResetRequest::whereIn('id', $this->selected)
                ->where('status', 'pending')
                ->with(['user.student', 'user.teacher']) // Eager load teacher too
                ->get();

            foreach ($requests as $request) {
                if ($this->processReset($request)) {
                    $count++;
                }
            }
        });

        $this->resetSelection();
        session()->flash('success', "$count permintaan berhasil disetujui. Password direset ke NIS (Siswa) atau 12345678 (Guru).");
    }

    // Bulk Reject
    public function bulkReject()
    {
        $count = PasswordResetRequest::whereIn('id', $this->selected)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        $this->resetSelection();
        session()->flash('success', "$count permintaan berhasil ditolak.");
    }

    public function resetSelection()
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function approve($id)
    {
        $request = PasswordResetRequest::findOrFail($id);
        
        if ($request->status !== 'pending') {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            if ($this->processReset($request)) {
                 session()->flash('success', 'Password berhasil direset menjadi NIS (Siswa) atau 12345678 (Guru).');
            } else {
                 session()->flash('error', 'Gagal: Data siswa/guru atau NIS tidak ditemukan.');
            }
        });
    }

    // Helper to process common reset logic
    private function processReset($request)
    {
        $user = User::with(['student', 'teacher'])->find($request->user_id);
        
        if (!$user) return false;

        $newPassword = null;

        if ($user->student && $user->student->nis) {
            $newPassword = $user->student->nis;
        } elseif ($user->teacher) {
            $newPassword = '12345678';
        }

        if ($newPassword) {
            $user->update([
                'password' => Hash::make($newPassword)
            ]);

            $request->update(['status' => 'approved']);
            return true;
        }

        return false;
    }

    public function reject($id)
    {
        $request = PasswordResetRequest::findOrFail($id);
        $request->update(['status' => 'rejected']);
        
        session()->flash('success', 'Permintaan ditolak.');
    }

    public function render()
    {
        $requests = PasswordResetRequest::with(['user.student', 'user.teacher'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.password-requests', [
            'requests' => $requests
        ])->layout('layouts.admin');
    }
}
