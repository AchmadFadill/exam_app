<div>
    <x-slot name="title">Kelola Kelas</x-slot>

    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-text-main">Data Kelas</h2>
            <p class="text-sm text-text-muted">Kelola grup kelas dan atur penempatan siswa.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" wire:model.live="search" class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all text-sm" placeholder="Cari nama kelas...">
            </div>
            <x-button wire:click="openAddModal" variant="primary" class="flex items-center gap-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Kelas
            </x-button>
        </div>
    </div>

    <!-- Class Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($classes as $class)
        <x-card class="relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                <div class="flex gap-2">
                    <button wire:click="openEditModal({{ $class['id'] }})" class="p-1.5 bg-white shadow-sm border border-gray-100 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </button>
                    <button wire:click="openDeleteModal({{ $class['id'] }})" class="p-1.5 bg-white shadow-sm border border-gray-100 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
            
            <div class="flex items-start gap-4">
                <div class="h-12 w-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-text-main line-clamp-1">{{ $class['name'] }}</h3>
                    <p class="text-sm text-text-muted mb-4">Kelas: {{ $class['level'] }}</p>
                    
                    <div class="flex items-center gap-2 text-sm text-text-muted mb-6">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <span>{{ $class['student_count'] }} Siswa Terdaftar</span>
                    </div>

                    <x-button wire:click="openAssignModal({{ $class['id'] }})" variant="secondary" class="w-full justify-center text-xs py-2">
                        Assign Siswa
                    </x-button>
                </div>
            </div>
        </x-card>
        @empty
        <div class="col-span-full py-12 text-center text-text-muted italic bg-white rounded-xl border border-dashed border-gray-200">
            Tidak ada data kelas ditemukan.
        </div>
        @endforelse
    </div>

    <!-- Add/Edit Modal -->
    @if($showAddModal || $showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showAddModal', false); $set('showEditModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-text-main">{{ $showAddModal ? 'Tambah Kelas Baru' : 'Edit Data Kelas' }}</h3>
                <button wire:click="$set('showAddModal', false); $set('showEditModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas</label>
                    <input type="text" wire:model="classForm.name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Contoh: X IPA 1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                    <select wire:model="classForm.level" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                        <option value="">Pilih Tingkatan Kelas</option>
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                </div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <x-button variant="secondary" wire:click="$set('showAddModal', false); $set('showEditModal', false)">Batal</x-button>
                <x-button variant="primary" wire:click="saveClass">Simpan Perubahan</x-button>
            </div>
        </div>
    </div>
    @endif

    <!-- Assign Students Modal -->
    @if($showAssignModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showAssignModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-text-main">Assign Siswa ke Kelas</h3>
                <button wire:click="$set('showAssignModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input type="text" wire:model.live="studentSearch" class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Cari nama atau NIS siswa...">
                </div>

                <div class="max-h-60 overflow-y-auto border border-gray-100 rounded-lg divide-y divide-gray-50">
                    @forelse($allStudents as $student)
                    <div class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors">
                        <input type="checkbox" wire:model="selectedStudents" value="{{ $student['id'] }}" class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-text-main">{{ $student['name'] }}</div>
                            <div class="text-xs text-text-muted">NIS: {{ $student['nis'] }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-text-muted italic text-sm">Siswa tidak ditemukan.</div>
                    @endforelse
                </div>
                
                @if(count($selectedStudents) > 0)
                <div class="p-2 bg-blue-50 rounded-lg border border-blue-100">
                    <p class="text-xs text-blue-700 font-medium text-center">{{ count($selectedStudents) }} siswa terpilih untuk diassign.</p>
                </div>
                @endif
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <x-button variant="secondary" wire:click="$set('showAssignModal', false)">Batal</x-button>
                <x-button variant="primary" wire:click="assignStudents" :disabled="count($selectedStudents) == 0">Simpan Penempatan</x-button>
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
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-xl font-bold text-text-main mb-2">Hapus Data Kelas?</h3>
                <p class="text-gray-500 text-sm">Menghapus kelas akan melepas semua siswa dari grup ini. Data nilai dan riwayat ujian siswa tetap aman.</p>
            </div>
            <div class="p-6 bg-gray-50 flex justify-center gap-3">
                <x-button variant="secondary" wire:click="$set('showDeleteModal', false)">Batal</x-button>
                <x-button variant="danger" wire:click="deleteClass">Ya, Hapus</x-button>
            </div>
        </div>
    </div>
    @endif
</div>
