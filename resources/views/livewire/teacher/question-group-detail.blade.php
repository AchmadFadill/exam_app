<div>
    <x-slot name="title">{{ $title }}</x-slot>

    <!-- Header with Back Button -->
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('teacher.questions') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                @if($renamingTitle)
                    <div class="flex items-center gap-2">
                        <input type="text" 
                               wire:model="newGroupTitle" 
                               wire:keydown.enter="updateGroupTitle"
                               class="text-xl font-bold text-text-main border-b-2 border-primary focus:outline-none bg-transparent px-1 py-0.5"
                               autofocus>
                        <button wire:click="updateGroupTitle" class="p-1 hover:bg-green-100 text-green-600 rounded-md transition-colors" title="Simpan">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </button>
                        <button wire:click="cancelRenaming" class="p-1 hover:bg-red-100 text-red-600 rounded-md transition-colors" title="Batal">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                @else
                    <div class="flex items-center gap-3 group">
                        <h2 class="text-2xl font-bold text-text-main">{{ $title }}</h2>
                        <button wire:click="startRenaming" class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-gray-100 rounded text-gray-400 hover:text-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                    </div>
                @endif
                <p class="text-sm text-gray-500 mt-1">{{ count($questions) }} Soal</p>
            </div>
        </div>
        <x-button variant="primary" wire:click="openAddModal">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Soal
        </x-button>
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
            <x-table.tr>
                <x-table.td colspan="6" class="py-20 text-center text-text-muted font-bold italic opacity-40">
                    Tidak ada soal ditemukan dalam kelompok ini.
                </x-table.td>
            </x-table.tr>
            @endforelse
        </tbody>
    </x-table>

    <!-- Add/Edit Modal -->
    @if($showAddModal || $showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto transform transition-all">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-text-main">{{ $showAddModal ? 'Tambah Soal' : 'Edit Soal' }}</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <!-- Question Group/Title (only show when editing) -->
                @if($showEditModal)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Judul Kelompok Soal <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.live="questionForm.title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all @error('questionForm.title') border-red-500 @enderror" placeholder="Contoh: Aljabar Dasar, Geometri, Trigonometri">
                    @error('questionForm.title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                @else
                <!-- Hidden input to maintain title value when adding -->
                <input type="hidden" wire:model="questionForm.title">
                @endif


                <!-- Subject Selection (only show when editing) -->
                @if($showEditModal)
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
                @else
                <!-- Hidden input to maintain subject value when adding -->
                <input type="hidden" wire:model="questionForm.subject_id">
                @endif

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

                <!-- Question Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Soal (Opsional)</label>
                    
                    @if($showEditModal && $editingImagePath)
                    <div class="mb-3 relative inline-block">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($editingImagePath) }}" 
                             class="max-w-xs rounded-lg border border-gray-300 shadow-sm">
                        <button type="button" 
                                wire:click="removeImage" 
                                class="absolute -top-2 -right-2 p-1 bg-red-600 text-white rounded-full hover:bg-red-700 shadow-md transition-colors"
                                title="Hapus Gambar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    @endif
                    
                    <input type="file" 
                           wire:model="questionImage" 
                           accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('questionImage') border-red-500 @enderror text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all">
                    
                    @error('questionImage') 
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p> 
                    @enderror
                    
                    @if($questionImage)
                    <div class="mt-2">
                        <p class="text-xs text-gray-500 mb-1">Preview:</p>
                        <img src="{{ $questionImage->temporaryUrl() }}" 
                             class="max-w-xs rounded-lg border border-gray-300 shadow-sm">
                    </div>
                    @endif
                    
                    <p class="mt-1 text-xs text-gray-500">Max 5MB. Format: JPG, PNG, GIF, SVG</p>
                </div>

                <!-- Multiple Choice Options -->
                @if($questionForm['type'] === 'multiple_choice')
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <label class="block text-sm font-medium text-gray-700">Opsi Jawaban <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            @if($optionCount < 4)
                            <button type="button" wire:click="addOption" class="text-xs px-3 py-1 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition-colors">
                                + Tambah Opsi
                            </button>
                            @endif
                            @if($optionCount > 2)
                            <button type="button" wire:click="removeOption" class="text-xs px-3 py-1 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors">
                                - Hapus Opsi
                            </button>
                            @endif
                        </div>
                    </div>
                    @php $labels = ['A', 'B', 'C', 'D', 'E']; @endphp
                    @for($index = 0; $index < $optionCount; $index++)
                    <div class="flex items-start gap-3">
                        <input type="radio" wire:model="questionForm.correct_option" value="{{ $labels[$index] }}" class="mt-3 text-primary focus:ring-primary" title="Pilih sebagai jawaban benar">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="flex items-center justify-center w-6 h-6 bg-gray-100 rounded-full text-xs font-bold">{{ $labels[$index] }}</span>
                                <input type="text" wire:model="questionForm.options.{{ $index }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all @error('questionForm.options.'.$index) border-red-500 @enderror" placeholder="Tulis opsi {{ $labels[$index] }}...">
                            </div>
                            @error('questionForm.options.'.$index) <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    @endfor
                    @error('questionForm.correct_option') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-500">💡 Pilih radio button di sebelah kiri untuk menandai jawaban yang benar</p>
                </div>
                @endif

                {{-- Question Score --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Skor/Bobot Soal <span class="text-red-500">*</span></label>
                    <input type="number" wire:model="questionForm.score" class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all @error('questionForm.score') border-red-500 @enderror" placeholder="10" min="1" max="100">
                    <p class="mt-1 text-xs text-gray-500">Nilai default untuk soal ini saat ditambahkan ke ujian (1-100)</p>
                    @error('questionForm.score') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Explanation -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pembahasan (Opsional)</label>
                    <textarea wire:model="questionForm.explanation" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Tulis pembahasan untuk membantu siswa memahami jawaban..."></textarea>
                    <p class="mt-1 text-xs text-gray-500">{{ strlen($questionForm['explanation'] ?? '') }}/1000 karakter</p>
                </div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <x-button variant="secondary" wire:click="closeModal">Batal</x-button>
                @if($showAddModal)
                <x-button variant="secondary" wire:click="saveAndAddAnother">Simpan & Tambah Lagi</x-button>
                @endif
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
</div>
