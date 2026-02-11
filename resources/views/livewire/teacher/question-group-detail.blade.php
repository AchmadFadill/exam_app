<div>
    <x-slot name="title">{{ $title }}</x-slot>

    <!-- Header with Back Button -->
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div class="flex items-center gap-3 sm:gap-4">
            <a href="{{ route('teacher.questions') }}" class="p-2 hover:bg-gray-100 rounded-xl transition-colors shrink-0 border border-border-subtle">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="min-w-0">
                @if($renamingTitle)
                    <div class="flex items-center gap-2">
                        <input type="text" 
                               wire:model="newGroupTitle" 
                               wire:keydown.enter="updateGroupTitle"
                               class="text-lg sm:text-xl font-black text-text-main border-b-2 border-primary focus:outline-none bg-transparent px-1 py-0.5 w-full max-w-[200px] sm:max-w-none"
                               autofocus>
                        <div class="flex gap-1 shrink-0">
                            <button wire:click="updateGroupTitle" class="p-1.5 hover:bg-green-100 text-green-600 rounded-lg transition-colors" title="Simpan">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </button>
                            <button wire:click="cancelRenaming" class="p-1.5 hover:bg-red-100 text-red-600 rounded-lg transition-colors" title="Batal">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-2 sm:gap-3 group">
                        <h2 class="text-xl sm:text-2xl font-black text-text-main truncate uppercase tracking-tight">{{ $title }}</h2>
                        <button wire:click="startRenaming" class="transition-opacity p-1 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-primary" title="Ubah Nama">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                    </div>
                @endif
                <div class="flex items-center gap-2.5 mt-0.5">
                    <div class="text-[10px] sm:text-sm font-bold text-gray-400 italic">{{ count($questions) }} Soal</div>
                    <span class="text-gray-300">•</span>
                    <div class="text-[10px] sm:text-sm font-black text-primary uppercase tracking-widest">Total: {{ $totalScore }} Poin</div>
                </div>
            </div>
        </div>
        <div class="flex gap-2 w-full lg:w-auto">
            <x-button variant="secondary" wire:click="distributeScores" class="flex-1 lg:flex-none flex items-center justify-center py-2.5 sm:py-3 text-[10px] sm:text-xs font-black uppercase tracking-widest" title="Otomatis bagi rata nilai soal jadi total 100">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                Distribusi (100)
            </x-button>
            <x-button variant="secondary" wire:click="exportGroupQuestions" class="flex-1 lg:flex-none flex items-center justify-center py-2.5 sm:py-3 text-[10px] sm:text-xs font-black uppercase tracking-widest" title="Export soal untuk kelompok ini">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export
            </x-button>
            <x-button variant="primary" wire:click="openAddModal" class="flex-1 lg:flex-none flex items-center justify-center py-2.5 sm:py-3 text-[10px] sm:text-xs font-black uppercase tracking-widest shadow-lg shadow-primary/20">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Soal
            </x-button>
        </div>
    </div>

    <!-- Bulk Action Bar -->
    @if(count($selectedQuestions) > 0)
    <div class="bg-primary text-white px-5 sm:px-6 py-3 sm:py-4 rounded-xl sm:rounded-lg shadow-lg flex items-center justify-between mb-4">
        <div class="flex items-center gap-2 sm:gap-3">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-xs sm:text-sm font-medium">{{ count($selectedQuestions) }} soal dipilih</span>
        </div>
        <button wire:click="$set('showBulkDeleteModal', true)" class="px-3 sm:px-4 py-1.5 sm:py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs sm:text-sm font-medium transition-colors flex items-center gap-1.5 sm:gap-2">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span class="hidden sm:inline">Hapus Terpilih</span>
            <span class="sm:hidden">Hapus</span>
        </button>
    </div>
    @endif

    <!-- Questions Table -->
    <x-table>
        <x-table.thead>
            <x-table.tr>
                <x-table.th class="w-12">
                    <input type="checkbox" 
                           wire:click="toggleSelectAll"
                           class="rounded border-gray-300 text-primary focus:ring-primary" 
                           title="Pilih Semua">
                </x-table.th>
                <x-table.th class="w-16">#</x-table.th>
                <x-table.th>Pertanyaan</x-table.th>
                <x-table.th>Tipe</x-table.th>
                <x-table.th>Bobot</x-table.th>
                <x-table.th>Mata Pelajaran</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </x-table.tr>
        </x-table.thead>
        <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
            @forelse($questions as $question)
            <x-table.tr>
                <x-table.td>
                    <input type="checkbox" wire:model.live="selectedQuestions" value="{{ $question->id }}" class="rounded border-gray-300 text-primary focus:ring-primary">
                </x-table.td>
                <x-table.td class="text-sm text-text-muted font-bold italic">{{ $loop->iteration }}</x-table.td>
                <x-table.td>
                    <p class="text-sm text-text-main font-medium line-clamp-2 leading-relaxed group-hover:text-primary transition-colors">{!! \Illuminate\Support\Str::limit(strip_tags($question->text), 150) !!}</p>
                    @if($question->image_path)
                    <div class="mt-2">
                        <a href="{{ $question->image_url }}" target="_blank" class="inline-block relative group/img">
                            <img src="{{ $question->image_url }}" class="h-16 w-auto rounded-xl border border-border-subtle object-cover shadow-sm group-hover/img:shadow-md transition-all">
                            <div class="absolute inset-0 bg-black/0 group-hover/img:bg-black/5 transition-colors rounded-xl"></div>
                        </a>
                    </div>
                    @endif
                </x-table.td>
                <x-table.td>
                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border whitespace-nowrap {{ $question->type === 'multiple_choice' ? 'bg-blue-500/10 text-blue-600 border-blue-500/20' : 'bg-purple-500/10 text-purple-600 border-purple-500/20' }}">
                        {{ $question->type === 'multiple_choice' ? 'PILIHAN GANDA' : 'ESSAY' }}
                    </span>
                </x-table.td>
                <x-table.td class="text-sm font-bold text-gray-700">{{ $question->score }}</x-table.td>
                <x-table.td class="text-xs font-black text-text-muted uppercase tracking-widest">{{ $question->subject->name }}</x-table.td>
                <x-table.td class="text-right">
                    <div class="flex justify-end gap-2 opacity-40 group-hover:opacity-100 transition-opacity">
                        <button wire:click="openEditModal({{ $question->id }})" class="p-2 text-primary hover:bg-primary/10 rounded-xl transition-all" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button wire:click="openDeleteModal({{ $question->id }})" class="p-2 text-red-600 hover:bg-red-500/10 rounded-xl transition-all" title="Hapus">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </x-table.td>
            </x-table.tr>
            @empty
            <x-empty-state 
                colspan="6" 
                title="Kelompok Kosong" 
                message="Belum ada soal dalam kelompok ini. Mari tambah soal baru! 🚀" 
                icon="folder-open" 
            />
            @endforelse
        </tbody>
    </x-table>

    <!-- Question Modals (Add & Edit) -->
    @foreach(['showAddModal' => false, 'showEditModal' => true] as $model => $isEdit)
        <x-question-modal 
            wire:model="{{ $model }}"
            :is-edit="$isEdit"
            :subjects="$subjects"
            :option-count="$optionCount"
            :editing-image-path="$editingImagePath"
            :question-image="$questionImage"
            :type="$questionForm['type']"
            :question-text="$questionForm['text']"
            :readonly-group="true"
        />
    @endforeach

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
                <h3 class="text-xl font-bold text-text-main mb-2">Hapus Soal?</h3>
                <p class="text-gray-500">Soal yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="p-6 bg-gray-50 flex justify-center gap-3">
                <x-button variant="secondary" wire:click="$set('showDeleteModal', false)">Batal</x-button>
                <x-button variant="danger" wire:click="deleteQuestion">Ya, Hapus</x-button>
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
                <h3 class="text-xl font-bold text-text-main mb-2">Hapus {{ count($selectedQuestions) }} Soal?</h3>
                <p class="text-gray-500">Soal yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="p-6 bg-gray-50 flex justify-center gap-3">
                <x-button variant="secondary" wire:click="$set('showBulkDeleteModal', false)">Batal</x-button>
                <x-button variant="danger" wire:click="bulkDelete">Ya, Hapus Semua</x-button>
            </div>
        </div>
    </div>
    @endif
    <x-latex-guide-modal />
</div>
