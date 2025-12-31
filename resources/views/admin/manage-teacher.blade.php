@section('title', 'Kelola Guru')
<div>
    <x-slot name="title">Kelola Guru</x-slot>

    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-text-main">Data Guru</h2>
            <p class="text-sm text-text-muted">Kelola informasi guru dan hak akses mereka di sini.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" wire:model.live="search" class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all text-sm" placeholder="Cari nama atau email...">
            </div>
            <x-button wire:click="openAddModal" variant="primary" class="flex items-center gap-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Guru
            </x-button>
        </div>
    </div>

    <!-- Teacher Table -->
    <x-card>
        <div class="overflow-x-auto -mx-6 -my-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 w-4">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            </div>
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500">Nama</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500">Email</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500">Mata Pelajaran</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($teachers as $teacher)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model.live="selectedTeachers" value="{{ $teacher['id'] }}" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                    {{ substr($teacher['name'], 0, 2) }}
                                </div>
                                <span class="font-medium text-text-main">{{ $teacher['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-muted">{{ $teacher['email'] }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-medium">
                                {{ $teacher['subject'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button wire:click="openResetPasswordModal({{ $teacher['id'] }})" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Reset Password">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </button>
                                <button wire:click="openEditModal({{ $teacher['id'] }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="openDeleteModal({{ $teacher['id'] }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Modals -->
    @if($showAddModal || $showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showAddModal', false); $set('showEditModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-text-main">{{ $showAddModal ? 'Tambah Guru Baru' : 'Edit Data Guru' }}</h3>
                <button wire:click="$set('showAddModal', false); $set('showEditModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" wire:model="teacherForm.name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Masukkan nama guru">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" wire:model="teacherForm.email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="guru@example.com">
                </div>
                @if($showAddModal)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" wire:model="teacherForm.password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Min. 8 karakter">
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran</label>
                    <select wire:model="teacherForm.subject" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                        <option value="">Pilih Mata Pelajaran</option>
                        <option value="Matematika">Matematika</option>
                        <option value="Bahasa Inggris">Bahasa Inggris</option>
                        <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                        <option value="Fisika">Fisika</option>
                        <option value="Kimia">Kimia</option>
                        <option value="Biologi">Biologi</option>
                        <option value="Sejarah">Sejarah</option>
                    </select>
                </div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <x-button variant="secondary" wire:click="$set('showAddModal', false); $set('showEditModal', false)">Batal</x-button>
                <x-button variant="primary" wire:click="saveTeacher">Simpan Perubahan</x-button>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showDeleteModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden transform transition-all">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-text-main mb-2">Hapus Data Guru?</h3>
                <p class="text-gray-500">Tindakan ini tidak dapat dibatalkan. Semua data terkait guru ini akan dihapus.</p>
            </div>
            <div class="p-6 bg-gray-50 flex justify-center gap-3">
                <x-button variant="secondary" wire:click="$set('showDeleteModal', false)">Batal</x-button>
                <x-button variant="danger" wire:click="deleteTeacher">Ya, Hapus</x-button>
            </div>
        </div>
    </div>
    @endif

    <!-- Reset Password Modal -->
    @if($showResetPasswordModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showResetPasswordModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden transform transition-all">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-text-main mb-2">Reset Password?</h3>
                <p class="text-gray-500">Password akan direset ke default (misalnya: 12345678). Guru disarankan segera mengubahnya.</p>
            </div>
            <div class="p-6 bg-gray-50 flex justify-center gap-3">
                <x-button variant="secondary" wire:click="$set('showResetPasswordModal', false)">Batal</x-button>
                <x-button variant="primary" class="bg-amber-600 hover:bg-amber-700" wire:click="resetPassword">Ya, Reset</x-button>
            </div>
        </div>
    </div>
    @endif

    <!-- Bulk Action Floating Bar -->
    @if(count($selectedTeachers) > 0)
    <div class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-white px-6 py-4 rounded-full shadow-2xl border border-gray-100 flex items-center gap-6 z-40 animate-bounce-in">
        <div class="flex items-center gap-2">
            <span class="bg-primary text-white text-xs font-bold px-2 py-1 rounded-full">{{ count($selectedTeachers) }}</span>
            <span class="text-sm font-medium text-gray-600">Guru Terpilih</span>
        </div>
        <div class="h-6 w-px bg-gray-200"></div>
        <button wire:click="openBulkResetPasswordModal" class="group flex items-center gap-2 text-sm font-semibold text-gray-700 hover:text-amber-600 transition-colors">
            <div class="p-1.5 rounded-full bg-gray-100 group-hover:bg-amber-100 text-gray-500 group-hover:text-amber-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            Reset Password Massal
        </button>
        <div class="h-6 w-px bg-gray-200"></div>
        <button wire:click="openBulkDeleteModal" class="group flex items-center gap-2 text-sm font-semibold text-gray-700 hover:text-red-600 transition-colors">
            <div class="p-1.5 rounded-full bg-gray-100 group-hover:bg-red-100 text-gray-500 group-hover:text-red-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            Hapus Massal
        </button>
    </div>
    @endif

    <!-- Bulk Reset Password Modal -->
    @if($showBulkResetPasswordModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showBulkResetPasswordModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden transform transition-all">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-text-main mb-2">Reset Password Massal?</h3>
                <p class="text-gray-500">Anda akan mereset password untuk <span class="font-bold">{{ count($selectedTeachers) }}</span> guru yang dipilih. Password akan kembali ke default.</p>
            </div>
            <div class="p-6 bg-gray-50 flex justify-center gap-3">
                <x-button variant="secondary" wire:click="$set('showBulkResetPasswordModal', false)">Batal</x-button>
                <x-button variant="primary" class="bg-amber-600 hover:bg-amber-700" wire:click="bulkResetPassword">Ya, Reset Semua</x-button>
            </div>
        </div>
    </div>
    @endif

    <!-- Bulk Delete Modal -->
    @if($showBulkDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showBulkDeleteModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden transform transition-all">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-text-main mb-2">Hapus Guru Massal?</h3>
                <p class="text-gray-500">Anda akan menghapus <span class="font-bold">{{ count($selectedTeachers) }}</span> guru yang dipilih. Data yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="p-6 bg-gray-50 flex justify-center gap-3">
                <x-button variant="secondary" wire:click="$set('showBulkDeleteModal', false)">Batal</x-button>
                <x-button variant="danger" wire:click="bulkDelete">Ya, Hapus Semua</x-button>
            </div>
        </div>
    </div>
    @endif
</div>
