<?php

namespace App\Livewire\Common;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileSettings extends Component
{
    use WithFileUploads;
    private const PHOTO_MAX_KB = 1024;

    public $current_password;
    public $password;
    public $password_confirmation;
    public $photo;

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($this->current_password, Auth::user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini salah.'],
            ]);
        }

        Auth::user()->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('password_success', 'Password berhasil diperbarui.');
    }

    public function updatedPhoto()
    {
        try {
            $this->validate([
                'photo' => 'image|max:' . self::PHOTO_MAX_KB, // 1MB max
            ], [
                'photo.image' => 'File harus berupa gambar.',
                'photo.max' => 'Ukuran foto maksimal 1 MB. Gunakan file di bawah 1 MB.',
            ]);
        } catch (ValidationException $e) {
            $this->photo = null;
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Upload gagal: ukuran foto maksimal 1 MB.',
            ]);
            throw $e;
        }
    }

    public function savePhoto()
    {
        $this->validate([
            'photo' => 'required|image|max:' . self::PHOTO_MAX_KB,
        ], [
            'photo.required' => 'Silakan pilih file foto terlebih dahulu.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 1 MB. Gunakan file di bawah 1 MB.',
        ]);

        $user = Auth::user();

        // Delete old photo if exists
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $this->photo->store('profile-photos', 'public');

        $user->update([
            'profile_photo_path' => $path,
        ]);

        $this->photo = null;
        session()->flash('photo_success', 'Foto profil berhasil diperbarui.');
    }

    public function deletePhoto()
    {
        $user = Auth::user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update([
                'profile_photo_path' => null,
            ]);
        }

        session()->flash('photo_success', 'Foto profil berhasil dihapus.');
    }

    public function render()
    {
        $role = Auth::user()->role;
        $layout = $role === 'student' ? 'layouts.student' : 'layouts.teacher';

        return view('livewire.common.profile-settings')
            ->layout($layout, ['title' => 'Pengaturan Profil']);
    }
}
