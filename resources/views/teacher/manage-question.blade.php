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

    <!-- Questions Table -->
    <x-card>
        <div class="overflow-x-auto -mx-6 -my-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500 w-16">#</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500">Pertanyaan</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500">Tipe</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500">Mata Pelajaran</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($questions as $question)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $loop->iteration + ($questions->currentPage() - 1) * $questions->perPage() }}</td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900 line-clamp-2">{!! \Illuminate\Support\Str::limit(strip_tags($question->text), 100) !!}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $question->type === 'multiple_choice' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                {{ $question->type === 'multiple_choice' ? 'Pilihan Ganda' : 'Essay' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $question->subject->name }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button wire:click="openEditModal({{ $question->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="openDeleteModal({{ $question->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-text-muted italic">
                            Tidak ada soal ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($questions->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $questions->links() }}
        </div>
        @endif
    </x-card>

    <!-- Add/Edit Modal -->
    @if($showAddModal || $showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto transform transition-all">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-text-main">{{ $showAddModal ? 'Tambah Soal Baru' : 'Edit Soal' }}</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <!-- Subject Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mata Pelajaran <span class="text-red-500">*</span></label>
                    <select wire:model="questionForm.subject_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all @error('questionForm.subject_id') border-red-500 @enderror">
                        <option value="">Pilih Mata Pelajaran</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('questionForm.subject_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Question Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Soal <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all {{ $questionForm['type'] === 'multiple_choice' ? 'border-primary bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" wire:model.live="questionForm.type" value="multiple_choice" class="mr-3">
                            <span class="font-medium">Pilihan Ganda</span>
                        </label>
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all {{ $questionForm['type'] === 'essay' ? 'border-primary bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" wire:model.live="questionForm.type" value="essay" class="mr-3">
                            <span class="font-medium">Essay</span>
                        </label>
                    </div>
                </div>

                <!-- Question Text -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pertanyaan <span class="text-red-500">*</span></label>
                    <textarea wire:model="questionForm.text" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all @error('questionForm.text') border-red-500 @enderror" placeholder="Tulis pertanyaan di sini..."></textarea>
                    @error('questionForm.text') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500">{{ strlen($questionForm['text']) }}/5000 karakter</p>
                </div>

                <!-- Multiple Choice Options -->
                @if($questionForm['type'] === 'multiple_choice')
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Opsi Jawaban <span class="text-red-500">*</span></label>
                    @php $labels = ['A', 'B', 'C', 'D', 'E']; @endphp
                    @foreach($labels as $index => $label)
                    <div class="flex items-start gap-3">
                        <input type="radio" wire:model="questionForm.correct_option" value="{{ $label }}" class="mt-3 text-primary focus:ring-primary" title="Pilih sebagai jawaban benar">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="flex items-center justify-center w-6 h-6 bg-gray-100 rounded-full text-xs font-bold">{{ $label }}</span>
                                <input type="text" wire:model="questionForm.options.{{ $index }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all @error('questionForm.options.'.$index) border-red-500 @enderror" placeholder="Tulis opsi {{ $label }}...">
                            </div>
                            @error('questionForm.options.'.$index) <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    @endforeach
                    @error('questionForm.correct_option') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-500">💡 Pilih radio button di sebelah kiri untuk menandai jawaban yang benar</p>
                </div>
                @endif

                <!-- Explanation -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pembahasan (Opsional)</label>
                    <textarea wire:model="questionForm.explanation" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Tulis pembahasan untuk membantu siswa memahami jawaban..."></textarea>
                    <p class="mt-1 text-xs text-gray-500">{{ strlen($questionForm['explanation'] ?? '') }}/1000 karakter</p>
                </div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <x-button variant="secondary" wire:click="closeModal">Batal</x-button>
                <x-button variant="primary" wire:click="saveQuestion">{{ $showAddModal ? 'Simpan' : 'Update' }}</x-button>
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
                        <p class="text-sm">Pastikan format Excel sesuai dengan template. Kolom: Mata Pelajaran, Tipe, Pertanyaan, Opsi A-E, Jawaban Benar, Pembahasan.</p>
                        <button type="button" wire:click="downloadTemplate" class="mt-2 inline-block font-bold underline text-sm">Download Template Excel</button>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload File Excel</label>
                    <input type="file" wire:model="importFile" accept=".xlsx,.xls" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-blue-700 cursor-pointer">
                    @error('importFile') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <x-button variant="secondary" wire:click="$set('showImportModal', false)">Batal</x-button>
                <x-button variant="primary" wire:click="importQuestions">Import</x-button>
            </div>
        </div>
    </div>
    @endif
</div>
