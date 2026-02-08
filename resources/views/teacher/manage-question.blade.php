<div>
    <x-slot name="title">Bank Soal</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <!-- Search Bar -->
        <div class="relative w-full sm:flex-1 sm:max-w-md">
            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none text-gray-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" wire:model.live="search" class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all text-sm" placeholder="Cari soal...">
        </div>
        
        <!-- Filters & Actions -->
        <div class="flex gap-2 flex-wrap w-full sm:w-auto">
            <select wire:model.live="filterSubject" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all text-sm">
                <option value="">Semua Mata Pelajaran</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterType" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all text-sm">
                <option value="">Semua Tipe</option>
                <option value="multiple_choice">Pilihan Ganda</option>
                <option value="essay">Essay</option>
            </select>

            <x-button wire:click="openImportModal" variant="secondary" class="flex items-center gap-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Import
            </x-button>

            <x-button wire:click="openAddModal" variant="primary" class="flex items-center gap-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Soal
            </x-button>
        </div>
    </div>

    <!-- Bulk Action Bar -->
    @if(count($selectedQuestions) > 0)
    <div class="bg-primary text-white px-6 py-4 rounded-lg shadow-lg flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ count($selectedQuestions) }} soal dipilih</span>
        </div>
        <button wire:click="$set('showBulkDeleteModal', true)" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Hapus Terpilih
        </button>
    </div>
    @endif

    <!-- Question Group Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($groupedQuestions as $title => $questions)
        <x-card class="hover:shadow-lg transition-shadow">
            <div class="p-6">
                <!-- Group Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-text-main mb-2">{{ $title }}</h3>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                {{ $questions->first()->subject->name }}
                            </span>
                            <span class="px-2.5 py-1 bg-blue-100 text-blue-600 text-xs font-semibold rounded-full">
                                {{ count($questions) }} soal
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Question Preview -->
                <div class="space-y-2 mb-4">
                    @foreach($questions->take(3) as $question)
                    <div class="flex items-start gap-2 text-sm text-gray-600">
                        <span class="text-primary font-medium">{{ $loop->iteration }}.</span>
                        <div class="flex-1 min-w-0">
                            <p class="line-clamp-1">{{ \Illuminate\Support\Str::limit(strip_tags($question->text), 50) }}</p>
                            @if($question->image_path)
                            <div class="mt-1">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Gambar
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @if(count($questions) > 3)
                    <p class="text-xs text-gray-400 italic">+ {{ count($questions) - 3 }} soal lainnya...</p>
                    @endif
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-gray-100">
                    <x-button href="{{ route('teacher.questions.group', ['title' => urlencode($title)]) }}" variant="primary" class="w-full">
                        Lihat Detail
                    </x-button>
                </div>
            </div>
        </x-card>
        @empty
        <div class="col-span-full">
            <x-card>
                <div class="px-6 py-12 text-center text-text-muted italic">
                    Tidak ada soal ditemukan. Klik "Tambah Soal" untuk membuat soal baru.
                </div>
            </x-card>
        </div>
        @endforelse
    </div>

    <!-- Add/Edit Modal (Handled by Child Component) -->
    <livewire:teacher.question.question-form />

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

    <!-- Import Modal -->
    @if($showImportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showImportModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-text-main">Import Soal dari Excel</h3>
                <button wire:click="$set('showImportModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-4 p-4 bg-blue-50 text-blue-700 rounded-lg mb-4">
                    <svg class="w-8 h-8 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold mb-1">Instruksi Import:</p>
                        <p class="text-sm">Pastikan format Excel sesuai dengan template. Kolom wajib: Mata Pelajaran, Tipe, Pertanyaan, Opsi A-D, Jawaban Benar, Pembahasan. <span class="font-semibold">Opsi E opsional</span> (kosongkan jika hanya butuh A-D).</p>
                        <button type="button" wire:click="downloadTemplate" class="mt-2 inline-block font-bold underline text-sm">Download Template Excel</button>
                    </div>
                </div>

                <!-- Title Input -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Judul Kelompok Soal <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="importTitle" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all @error('importTitle') border-red-500 @enderror" placeholder="Contoh: UTS Matematika Semester 1">
                    <p class="mt-1 text-xs text-gray-500">Semua soal yang diimport akan masuk ke kelompok ini</p>
                    @error('importTitle') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload File Excel</label>
                    <input type="file" wire:model="importFile" accept=".xlsx,.xls" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-blue-700 cursor-pointer">
                    @error('importFile') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <x-button variant="secondary" wire:click="$set('showImportModal', false)">Batal</x-button>
                <x-button variant="primary" wire:click="importQuestions">Import</x-button>
            </div>
        </div>
    </div>
    @endif

    <!-- Pagination -->
    <div class="mt-6">
        {{ $paginatedTitles->links() }}
    </div>
</div>
