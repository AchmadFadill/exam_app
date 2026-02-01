<div>
    <x-header title="Data Pengajar" subtitle="Kelola dan pantau seluruh data pengajar akademik.">
        <x-button wire:click="exportTeachers" variant="secondary" class="font-black uppercase text-[10px] tracking-widest px-6 py-3">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export
        </x-button>
        <x-button wire:click="openImportModal" variant="secondary" class="font-black uppercase text-[10px] tracking-widest px-6 py-3">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Import
        </x-button>
        <x-button wire:click="openAddModal" variant="primary" class="font-black uppercase text-[10px] tracking-widest px-6 py-3">
            <svg class="w-4 h-4 mr-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Guru
        </x-button>
    </x-header>

    <div class="mb-10 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6">
        <!-- Search Bar (Left - fills space) -->
        <div class="relative w-full sm:flex-1 sm:max-w-lg group">
            <span class="absolute left-5 top-1/2 transform -translate-y-1/2 pointer-events-none text-text-muted group-focus-within:text-primary transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" wire:model.live="search" class="pl-14 w-full px-6 py-4 bg-bg-surface dark:bg-slate-800/50 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all text-sm font-bold tracking-tight shadow-sm" placeholder="Cari nama atau email pengajar...">
        </div>
    </div>

    <!-- Teacher Table -->
    <x-table>
        <x-table.thead>
            <x-table.tr>
                <x-table.th class="w-4">
                    <div class="flex items-center justify-center">
                        <input type="checkbox" wire:model.live="selectAll" class="w-5 h-5 text-primary border-border-main dark:border-slate-700 rounded-lg focus:ring-primary/20 bg-bg-surface dark:bg-slate-800">
                    </div>
                </x-table.th>
                <x-table.th>Nama Pengajar</x-table.th>
                <x-table.th>Email</x-table.th>
                <x-table.th>Mata Pelajaran</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </x-table.tr>
        </x-table.thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($teachers as $teacher)
            <x-table.tr>
                <x-table.td class="text-center">
                    <div class="flex items-center justify-center">
                        <input type="checkbox" wire:model.live="selectedTeachers" value="{{ $teacher->id }}" class="w-5 h-5 text-primary border-border-main dark:border-slate-700 rounded-lg focus:ring-primary/20 bg-bg-surface dark:bg-slate-800">
                    </div>
                </x-table.td>
                <x-table.td>
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center font-black text-xs shadow-inner group-hover:scale-110 transition-transform">
                            {{ substr($teacher->user->name, 0, 2) }}
                        </div>
                        <span class="font-black text-text-main tracking-tight uppercase text-sm group-hover:text-primary transition-colors">{{ $teacher->user->name }}</span>
                    </div>
                </x-table.td>
                <x-table.td class="font-bold text-text-muted">{{ $teacher->user->email }}</x-table.td>
                <x-table.td>
                    <span class="px-3 py-1 rounded-full bg-blue-50 dark:bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest">
                        {{ $teacher->subject?->name ?? '-' }}
                    </span>
                </x-table.td>
                <x-table.td class="text-right">
                    <div class="flex justify-end gap-3 opacity-40 group-hover:opacity-100 transition-opacity">
                        <x-button wire:click="openResetPasswordModal({{ $teacher->id }})" variant="warning" size="sm" square="true" title="Reset Password">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </x-button>
                        <x-button wire:click="openEditModal({{ $teacher->id }})" variant="primary" size="sm" square="true" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </x-button>
                        <x-button wire:click="openDeleteModal({{ $teacher->id }})" variant="danger" size="sm" square="true" title="Hapus">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </x-button>
                    </div>
                </x-table.td>
            </x-table.tr>
            @empty
            <x-table.tr>
                <x-table.td colspan="5" class="py-20 text-center text-text-muted font-bold italic opacity-60">Tidak ada data koleksi pengajar ditemukan.</x-table.td>
            </x-table.tr>
            @endforelse
        </tbody>
        <x-slot name="after">
            @if($teachers->hasPages())
            <div class="px-6 py-4 border-t border-border-subtle dark:border-border-subtle bg-gray-50/30 dark:bg-slate-800/20">
                {{ $teachers->links() }}
            </div>
            @endif
        </x-slot>
    </x-table>

    <!-- Modals -->
    @if($showAddModal || $showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-md transition-all" wire:click="$set('showAddModal', false); $set('showEditModal', false)"></div>
        <div class="relative bg-bg-surface dark:bg-slate-900 rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden transform transition-all border border-white/5">
            <div class="px-10 py-8 border-b border-border-subtle dark:border-border-subtle flex justify-between items-center bg-gray-50/50 dark:bg-slate-800/30">
                <div>
                    <h3 class="text-xl font-black text-text-main tracking-tight uppercase italic">{{ $showAddModal ? 'Tambah Guru' : 'Edit Data Guru' }}</h3>
                    
                 </div>
                <x-button wire:click="$set('showAddModal', false); $set('showEditModal', false)" variant="secondary" size="sm" square="true" class="!rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </x-button>
            </div>
            <div class="p-10 space-y-8">
                <div>
                    <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70">Nama Lengkap</label>
                    <input type="text" wire:model="teacherForm.name" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold" placeholder="Contoh: Dr. Budi Santoso">
                </div>
                <div>
                    <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70">Alamat Email</label>
                    <input type="email" wire:model="teacherForm.email" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold" placeholder="guru@institusi.ac.id">
                </div>
                @if($showAddModal)
                <div>
                    <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70">Password</label>
                    <input type="password" wire:model="teacherForm.password" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold" placeholder="Min. 8 Karakter Unik">
                </div>
                @endif
                <div>
                    <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70">Mata Pelajaran yang Diampu</label>
                    <select wire:model="teacherForm.subject_id" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold appearance-none bg-no-repeat bg-[right_1.5rem_center] bg-[length:1em_1em]" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22currentColor%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222.5%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E')">
                        <option value="">Pilih Mata Pelajaran</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="px-10 py-8 bg-gray-50/50 dark:bg-slate-800/30 border-t border-border-subtle dark:border-border-subtle flex justify-end gap-4">
                <x-button variant="secondary" wire:click="$set('showAddModal', false); $set('showEditModal', false)" class="font-black uppercase text-[10px] tracking-widest">Batal</x-button>
                <x-button variant="primary" wire:click="saveTeacher" class="font-black uppercase text-[10px] tracking-widest px-8">Simpan Data</x-button>
            </div>
        </div>
    </div>
    @endif

    <!-- Import Modal -->
    @if($showImportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showImportModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-text-main">Import Guru dari Excel</h3>
                <x-button wire:click="$set('showImportModal', false)" variant="secondary" size="sm" square="true" class="!rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </x-button>
            </div>
            <div class="p-6 space-y-4">
                <div class="p-4 bg-blue-50 text-blue-700 rounded-lg text-sm flex items-start gap-3">
                    <svg class="h-5 w-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold mb-1">Instruksi Import:</p>
                        <p>Kolom wajib: <strong>nama</strong>, <strong>email</strong>. Kolom opsional: <strong>mata_pelajaran</strong> (kode mapel). Password default: password123.</p>
                        <x-button type="button" wire:click="downloadTemplate" variant="soft" size="xs" class="mt-2">Download Template Excel</x-button>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel (.xlsx, .xls)</label>
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-8 text-center hover:border-primary transition-colors cursor-pointer group">
                        <input type="file" wire:model="importFile" class="hidden" id="fileImport">
                        <label for="fileImport" class="cursor-pointer">
                            <svg class="h-10 w-10 text-gray-400 group-hover:text-primary mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="text-sm text-gray-600">Klik untuk upload atau drag and drop</p>
                            <p class="text-xs text-gray-400 mt-1">Maksimal file 2MB</p>
                        </label>
                    </div>
                    @if($importFile)
                        <div class="mt-3 flex items-center justify-between p-2 bg-green-50 rounded border border-green-100">
                            <span class="text-xs text-green-700 truncate">{{ $importFile->getClientOriginalName() }}</span>
                            <x-button wire:click="$set('importFile', null)" variant="danger" size="xs" square="true" class="!rounded-lg">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </x-button>
                        </div>
                    @endif
                </div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <x-button variant="secondary" wire:click="$set('showImportModal', false)">Batal</x-button>
                <x-button variant="primary" wire:click="importTeachers">Import Sekarang</x-button>
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
    <div class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-slate-900 dark:bg-slate-800 px-10 py-5 rounded-[2rem] shadow-2xl border border-white/10 flex items-center gap-10 z-40 animate-bounce-in ring-4 ring-primary/20">
        <div class="flex items-center gap-4">
            <span class="bg-primary text-white text-xs font-black px-3 py-1.5 rounded-xl shadow-lg">{{ count($selectedTeachers) }}</span>
            <span class="text-xs font-black text-white uppercase tracking-widest opacity-80">Guru Dipilih</span>
        </div>
        <div class="h-8 w-px bg-white/10"></div>
        <x-button wire:click="openBulkResetPasswordModal" variant="warning" size="sm" class="uppercase tracking-widest px-6 py-3">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            Reset Password
        </x-button>
        <div class="h-8 w-px bg-white/10"></div>
        <x-button wire:click="openBulkDeleteModal" variant="danger" size="sm" class="uppercase tracking-widest px-6 py-3">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Hapus Massal
        </x-button>
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
