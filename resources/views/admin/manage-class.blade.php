<div>
    <x-header title="Data Kelas" subtitle="Kelola dan pantau seluruh unit rombongan belajar.">
        <x-button wire:click="openAddModal" variant="primary" class="font-black uppercase text-[10px] tracking-widest px-8 py-3.5">
            <svg class="w-4 h-4 mr-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
            </svg>
            Buat Kelas
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
            <input type="text" wire:model.live="search" class="pl-14 w-full px-6 py-4 bg-bg-surface dark:bg-slate-800/50 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all text-sm font-bold tracking-tight shadow-sm" placeholder="Cari kelas...">
        </div>
    </div>

    <!-- Class Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($classes as $class)
        <x-card class="relative overflow-hidden group hover:border-primary/40">
            <div class="absolute top-0 right-0 p-5 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                <div class="flex gap-2">
                    <x-button wire:click="openEditModal({{ $class['id'] }})" variant="primary" size="sm" square="true" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </x-button>
                    <x-button wire:click="openDeleteModal({{ $class['id'] }})" variant="danger" size="sm" square="true" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </x-button>
                </div>
            </div>
            
            <div class="flex items-start gap-6">
                <div class="h-16 w-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-inner">
                    <svg class="w-8 h-8 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-2xl font-black text-text-main tracking-tight uppercase truncate group-hover:text-primary transition-colors">{{ $class['name'] }}</h3>
                    <p class="text-[10px] font-black  mt-1 uppercase">Wali Kelas : {{ $class['teacher_name'] ?? 'Belum ada' }}</p>

                    <div class="flex items-center gap-2.5 text-sm text-text-muted mt-4 mb-8 font-bold">
                        <svg class="w-5 h-5 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <span>{{ $class['student_count'] }} Siswa</span>
                    </div>

                    <x-button wire:click="openAssignModal({{ $class['id'] }})" variant="secondary" class="w-full font-black uppercase text-[10px] tracking-[0.2em] py-3 rounded-xl border-dashed">
                        Kelola Siswa 
                    </x-button>
                </div>
            </div>
        </x-card>
        @empty
        <x-empty-state 
            colspan="full" 
            title="Kelas Tidak Terdaftar" 
            message="Unit rombongan belajar belum didefinisikan dalam sistem atau tidak ditemukan." 
            icon="folder-open" 
        />
        @endforelse
    </div>

    @if($showAddModal || $showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-md transition-all" wire:click="closeModal"></div>
        <div class="relative bg-bg-surface dark:bg-slate-900 rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden transform transition-all border border-white/5">
            <div class="px-10 py-8 border-b border-border-subtle dark:border-border-subtle flex justify-between items-center bg-gray-50/50 dark:bg-slate-800/30">
                <div>
                     <h3 class="text-xl font-black text-text-main tracking-tight uppercase">{{ $showAddModal ? 'Tambah kelas' : 'Edit Kelas' }}</h3>
                    
                </div>
                <x-button wire:click="closeModal" variant="secondary" size="sm" square="true" class="!rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                </x-button>
            </div>
            <div class="p-10 space-y-8">
                <div>
                    <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70">Identitas Kelas</label>
                    <input type="text" wire:model="classForm.name" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold" placeholder="Contoh: XII - TEKNIK INFORMATIKA">
                </div>
                <div>
                    <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70">Tingkat Kelas</label>
                    <select wire:model="classForm.level" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold appearance-none bg-no-repeat bg-[right_1.5rem_center] bg-[length:1em_1em]" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22currentColor%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222.5%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E')">
                        <option value="">Pilih Tingkatan kelas</option>
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70">Wali Kelas</label>
                    <select wire:model="classForm.teacher_id" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold appearance-none bg-no-repeat bg-[right_1.5rem_center] bg-[length:1em_1em]" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22currentColor%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222.5%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E')">
                        <option value="">Pilih Wali Kelas</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="px-10 py-8 bg-gray-50/50 dark:bg-slate-800/30 border-t border-border-subtle dark:border-border-subtle flex justify-end gap-4">
                <x-button variant="secondary" wire:click="closeModal" class="font-black text-[10px] uppercase tracking-widest">Batal</x-button>
                <x-button variant="primary" wire:click="saveClass" class="font-black text-[10px] uppercase tracking-widest px-8">Simpan Perubahan</x-button>
            </div>
        </div>
    </div>
    @endif

    @if($showAssignModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-md transition-all" wire:click="$set('showAssignModal', false)"></div>
        <div class="relative bg-bg-surface dark:bg-slate-900 rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden transform transition-all border border-white/5">
            <div class="px-10 py-8 border-b border-border-subtle dark:border-border-subtle flex justify-between items-center bg-gray-50/50 dark:bg-slate-800/30">
                <div>
                    <h3 class="text-xl font-black text-text-main tracking-tight uppercase">Kelola Anggota Kelas</h3>
                    <p class="text-[10px] text-text-muted font-bold tracking-[0.2em] mt-1 uppercase opacity-60"></p>
                </div>
                <x-button wire:click="$set('showAssignModal', false)" variant="secondary" size="sm" square="true" class="!rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                </x-button>
            </div>
            <div class="p-10 space-y-8">
                <div class="relative group">
                    <span class="absolute inset-y-0 left-5 flex items-center text-text-muted group-focus-within:text-primary transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input type="text" wire:model.live="studentSearch" class="pl-14 w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold" placeholder="Filter identitas atau NIS...">
                </div>

                <div class="max-h-64 overflow-y-auto border border-border-main dark:border-slate-700 rounded-2xl divide-y divide-border-subtle dark:divide-slate-800 shadow-inner bg-gray-50/50 dark:bg-slate-900/50">
                    @forelse($allStudents as $student)
                    <div class="flex items-center gap-5 p-5 hover:bg-gray-50/50 dark:hover:bg-slate-800/30 transition-all cursor-pointer group {{ $student['current_class_id'] && $student['current_class_id'] != $selectedClass ? 'bg-amber-500/5' : '' }}">
                        <div class="flex items-center justify-center">
                            <input type="checkbox" wire:model.live="selectedStudents" value="{{ $student['id'] }}" class="w-6 h-6 text-primary border-border-main dark:border-slate-700 rounded-lg focus:ring-primary/20 bg-bg-surface dark:bg-slate-800">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-black text-text-main tracking-tight uppercase group-hover:text-primary transition-colors">{{ $student['name'] }}</div>
                            <div class="text-[10px] font-bold text-text-muted flex items-center gap-3 mt-1 uppercase tracking-widest opacity-60">
                                <span>NIS: {{ $student['nis'] }}</span>
                                @if($student['current_class'])
                                    <span class="px-2 py-0.5 rounded text-[8px] font-black {{ $student['current_class_id'] == $selectedClass ? 'bg-green-100 dark:bg-green-500/20 text-green-600' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-600' }}">
                                        {{ $student['current_class'] }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-slate-800 text-text-muted text-[8px] font-black">Unassigned</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
            <x-empty-state 
                colspan="5" 
                title="Daftar Kelas Kosong" 
                message="Belum ada data kelas yang ditambahkan ke dalam sistem." 
                icon="folder-open" 
            />
            @endforelse
                </div>
                
                @if(count($selectedStudents) > 0)
                <div class="p-4 bg-primary/5 rounded-2xl border border-primary/10 flex items-center gap-4">
                    <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
                    <p class="text-xs text-primary font-black uppercase tracking-widest">{{ count($selectedStudents) }} Peserta terpilih untuk migrasi.</p>
                </div>
                @endif
            </div>
            <div class="px-10 py-8 bg-gray-50/50 dark:bg-slate-800/30 border-t border-border-subtle dark:border-border-subtle flex justify-end gap-4">
                <x-button variant="secondary" wire:click="$set('showAssignModal', false)" class="font-black text-[10px] uppercase tracking-widest">Batal</x-button>
                <x-button variant="primary" wire:click="assignStudents" class="font-black text-[10px] uppercase tracking-widest px-8">Simpan Perubahan</x-button>
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
