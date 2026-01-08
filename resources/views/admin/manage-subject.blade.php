<div>
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <!-- Search Bar (Left - fills space) -->
        <div class="relative w-full sm:flex-1 sm:max-w-lg">
            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none text-gray-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" wire:model.live="search" class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all text-sm" placeholder="Cari mapel atau kode...">
        </div>
        
        <!-- Action Button (Right) -->
        <x-button wire:click="openAddModal" variant="primary" class="flex items-center gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Mapel
        </x-button>
    </div>

    <!-- Subject Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($subjects as $subject)
        <x-card class="relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                <div class="flex gap-2">
                    <button wire:click="openEditModal({{ $subject['id'] }})" class="p-1.5 bg-white shadow-sm border border-gray-100 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </button>
                    <button wire:click="openDeleteModal({{ $subject['id'] }})" class="p-1.5 bg-white shadow-sm border border-gray-100 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
            
            <div class="flex items-start gap-4">
                <div class="h-12 w-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center shrink-0 font-bold text-sm">
                    {{ $subject['code'] }}
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-text-main line-clamp-1 mb-1">{{ $subject['name'] }}</h3>
                    
                    <div class="flex items-center gap-2 text-sm text-text-muted mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        @if($subject['teacher'] !== '-')
                            <span class="text-text-main font-medium">{{ $subject['teacher'] }}</span>
                        @else
                            <span class="text-red-500 text-xs italic">Belum ada guru</span>
                        @endif
                    </div>

                    <x-button wire:click="openAssignModal({{ $subject['id'] }})" variant="secondary" class="w-full justify-center text-xs py-2">
                        {{ $subject['teacher'] !== '-' ? 'Ganti Guru Pengampu' : 'Assign Guru' }}
                    </x-button>
                </div>
            </div>
        </x-card>
        @empty
        <div class="col-span-full py-12 text-center text-text-muted italic bg-white rounded-xl border border-dashed border-gray-200">
            Tidak ada mata pelajaran ditemukan.
        </div>
        @endforelse
    </div>

    <!-- Add/Edit Modal -->
    @if($showAddModal || $showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showAddModal', false); $set('showEditModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-text-main">{{ $showAddModal ? 'Tambah Mata Pelajaran' : 'Edit Mata Pelajaran' }}</h3>
                <button wire:click="$set('showAddModal', false); $set('showEditModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mata Pelajaran</label>
                    <input type="text" wire:model="subjectForm.name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Contoh: Matematika Wajib">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Mapel</label>
                    <input type="text" wire:model="subjectForm.code" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all uppercase" placeholder="Contoh: MTK-W">
                </div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <x-button variant="secondary" wire:click="$set('showAddModal', false); $set('showEditModal', false)">Batal</x-button>
                <x-button variant="primary" wire:click="saveSubject">Simpan</x-button>
            </div>
        </div>
    </div>
    @endif

    <!-- Assign Teacher Modal -->
    @if($showAssignModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showAssignModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-text-main">Pilih Guru Pengampu</h3>
                <button wire:click="$set('showAssignModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input type="text" wire:model.live="teacherSearch" class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Cari nama guru...">
                </div>

                <div class="max-h-60 overflow-y-auto border border-gray-100 rounded-lg divide-y divide-gray-50">
                    @forelse($teachers as $teacher)
                    <div class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors cursor-pointer" wire:click="$set('selectedTeacher', {{ $teacher['id'] }})">
                        <div class="flex items-center justify-center h-5 w-5 rounded-full border {{ $selectedTeacher === $teacher['id'] ? 'border-primary bg-primary text-white' : 'border-gray-300' }}">
                            @if($selectedTeacher === $teacher['id'])
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-text-main">{{ $teacher['name'] }}</div>
                            <div class="text-xs text-text-muted">{{ $teacher['email'] }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-text-muted italic text-sm">Guru tidak ditemukan.</div>
                    @endforelse
                </div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <x-button variant="secondary" wire:click="$set('showAssignModal', false)">Batal</x-button>
                <x-button variant="primary" wire:click="assignTeacher" :disabled="!$selectedTeacher">Simpan</x-button>
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
                <h3 class="text-xl font-bold text-text-main mb-2">Hapus Mata Pelajaran?</h3>
                <p class="text-gray-500 text-sm">Tindakan ini tidak dapat dibatalkan. Data ujian yang terkait dengan mapel ini juga mungkin terpengaruh.</p>
            </div>
            <div class="p-6 bg-gray-50 flex justify-center gap-3">
                <x-button variant="secondary" wire:click="$set('showDeleteModal', false)">Batal</x-button>
                <x-button variant="danger" wire:click="deleteSubject">Ya, Hapus</x-button>
            </div>
        </div>
    </div>
    @endif
</div>
