@props([
    'isOpen' => false,
    'isEdit' => false,
    'subjects' => [],
    'optionCount' => 5,
    'editingImagePath' => null,
    'questionImage' => null,
    'type' => 'multiple_choice',
    'showSubject' => true,
    'showTitle' => true,
    'readonlyGroup' => false,
])

<div x-data="{ show: @entangle($attributes->wire('model')->value() ?? 'isOpen') }" 
     x-show="show" 
     x-cloak
     class="fixed inset-0 z-[70] overflow-y-auto" 
     style="display: none;">
     
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 transition-opacity bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-sm" 
             @click="show = false; {{ (method_exists($this, 'closeModal') ? '$wire.closeModal()' : '$wire.closeQuestionModal()') }}" ></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="show" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="relative z-10 inline-block align-bottom bg-bg-surface dark:bg-slate-900 rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-border-main dark:border-border-main">
            
            <div class="px-8 pt-8 pb-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-8">
                     <div>
                        <h3 class="text-2xl font-black text-text-main tracking-tight italic uppercase">
                            {{ ($isEdit ? 'Edit' : 'Tambah') }} <span class="text-primary not-italic">Soal</span>
                        </h3>
                        <p class="text-[10px] font-black text-text-muted uppercase tracking-[0.2em] mt-1 opacity-60">
                            {{ ($isEdit ? 'Perbarui data soal ujian' : 'Buat pertanyaan baru untuk bank soal') }}
                        </p>
                    </div>
                    <button @click="show = false; {{ (method_exists($this, 'closeModal') ? '$wire.closeModal()' : '$wire.closeQuestionModal()') }}" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-400 hover:text-primary transition-all">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-8 max-h-[70vh] overflow-y-auto px-2 custom-scrollbar">
                    <!-- Config Row -->
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                         <!-- Left Col -->
                         <div class="space-y-6">
                            @if($showTitle)
                            <div>
                                <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Judul Kelompok Soal <span class="text-red-500">*</span></label>
                                <input type="text" 
                                       wire:model="questionForm.title" 
                                       @if($readonlyGroup) disabled @endif
                                       class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold tracking-tight shadow-inner disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-200/50 dark:disabled:bg-slate-900/50" 
                                       placeholder="Contoh: UTS Matematika, Bab Aljabar">
                                @error('questionForm.title') <p class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>
                            @endif
                            
                            @if($showSubject && !auth()->user()?->isTeacher())
                            <div>
                                <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Mata Pelajaran <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <select wire:model="questionForm.subject_id" 
                                            @if($readonlyGroup) disabled @endif
                                            class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold appearance-none bg-no-repeat bg-[right_1.5rem_center] bg-[length:1em_1em] shadow-inner disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-200/50 dark:disabled:bg-slate-900/50" 
                                            style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22currentColor%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222.5%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E')">
                                        <option value="">Pilih Mapel</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('questionForm.subject_id') <p class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>
                            @elseif($showSubject)
                            <div>
                                <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Mata Pelajaran</label>
                                <div class="w-full px-6 py-4 bg-gray-100/70 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl font-bold shadow-inner text-text-main">
                                    {{ optional(collect($subjects)->firstWhere('id', (int) data_get($this, 'questionForm.subject_id')))->name ?? (optional(collect($subjects)->first())->name ?? '-') }}
                                </div>
                                <p class="mt-2 text-[9px] font-bold text-text-muted uppercase tracking-widest">Mapel otomatis sesuai yang diampu</p>
                                @error('questionForm.subject_id') <p class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>
                            @endif
                         </div>

                         <!-- Right Col -->
                         <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Tipe Soal</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model.live="questionForm.type" value="multiple_choice" class="peer sr-only">
                                        <div class="p-4 rounded-2xl border border-border-main dark:border-border-main bg-white dark:bg-slate-800 peer-checked:bg-primary/5 peer-checked:border-primary peer-checked:text-primary transition-all text-center hover:bg-gray-50 dark:hover:bg-slate-700">
                                            <span class="text-xs font-black uppercase tracking-widest">Pilihan Ganda</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model.live="questionForm.type" value="essay" class="peer sr-only">
                                        <div class="p-4 rounded-2xl border border-border-main dark:border-border-main bg-white dark:bg-slate-800 peer-checked:bg-primary/5 peer-checked:border-primary peer-checked:text-primary transition-all text-center hover:bg-gray-50 dark:hover:bg-slate-700">
                                            <span class="text-xs font-black uppercase tracking-widest">Essay</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Bobot Nilai</label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" max="100" wire:model="questionForm.score" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold shadow-inner" placeholder="10">
                                    <span class="absolute right-6 top-1/2 -translate-y-1/2 text-xs font-black text-text-muted opacity-40 uppercase tracking-widest">Poin</span>
                                </div>
                                @error('questionForm.score') <p class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>
                         </div>
                     </div>

                    <!-- Question Text -->
                    <div x-data="latexPreview(@js($attributes->get('question-text') ?? ''))" x-init="init()">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <label class="block text-xs font-black text-text-main uppercase tracking-widest opacity-70 italic">Pertanyaan <span class="text-red-500">*</span></label>
                            <button type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-primary/20 bg-primary/5 text-primary text-[10px] font-black uppercase tracking-widest hover:bg-primary/10"
                                    @click="$dispatch('open-latex-guide')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3v2.25m4.5-2.25v2.25M9 9h6m-8.25 9h10.5A2.25 2.25 0 0019.5 15.75V8.25A2.25 2.25 0 0017.25 6H6.75A2.25 2.25 0 004.5 8.25v7.5A2.25 2.25 0 006.75 18z" />
                                </svg>
                                Panduan Rumus
                            </button>
                        </div>
                        <textarea wire:model.live.debounce.300ms="questionForm.text"
                                  rows="5"
                                  data-latex-enabled="1"
                                  @focus="window.setLatexActiveInput($event.target)"
                                  @input="update($event.target.value)"
                                  class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-medium leading-relaxed shadow-inner"
                                  placeholder="Tulis pertanyaan disini..."></textarea>
                        @error('questionForm.text') <p class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                        <div class="mt-3 rounded-xl border border-blue-100 dark:border-blue-900/40 bg-blue-50/70 dark:bg-slate-800/60 p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-300 mb-2">Pratinjau Rumus</p>
                            <div x-ref="preview" class="text-sm text-slate-700 dark:text-slate-200 min-h-8 break-words"></div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="p-6 rounded-[2rem] bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-slate-700">
                         <label class="block text-xs font-black text-text-main mb-4 uppercase tracking-widest opacity-70 italic">Gambar Soal (Opsional)</label>
                         
                         <div class="flex items-start gap-6">
                             @if($editingImagePath && !$questionImage)
                                <div class="relative group shrink-0">
                                    <img src="{{ Storage::url($editingImagePath) }}" class="h-32 w-32 object-cover rounded-2xl border-2 border-white dark:border-slate-700 shadow-md">
                                    <button wire:click="removeImage" type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 shadow-lg hover:bg-red-600 transition-transform hover:scale-110">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                             @endif

                             @if($questionImage)
                                <div class="relative group shrink-0">
                                    <img src="{{ $questionImage->temporaryUrl() }}" class="h-32 w-32 object-cover rounded-2xl border-2 border-primary shadow-md">
                                    <div class="absolute inset-0 bg-black/20 rounded-2xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="text-white text-xs font-bold uppercase tracking-widest bg-black/50 px-2 py-1 rounded">Baru</span>
                                    </div>
                                </div>
                             @endif

                             <div class="flex-1">
                                 <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-2xl cursor-pointer bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors group">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-3 text-gray-400 group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <p class="mb-1 text-xs text-gray-500"><span class="font-bold text-primary">Klik upload</span> atau drag and drop</p>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-widest">PNG, JPG, GIF (Max 5MB)</p>
                                    </div>
                                    <input type="file" wire:model="questionImage" class="hidden" accept="image/*">
                                </label>
                                 @error('questionImage') <p class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                             </div>
                         </div>
                    </div>

                    <!-- Options Section -->
                    @if($type === 'multiple_choice')
                        <div class="space-y-6 pt-6 border-t border-gray-100 dark:border-slate-800" x-data="latexPreview('')" x-init="init()">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <label class="block text-xs font-black text-text-main uppercase tracking-widest opacity-70 italic">Opsi Jawaban</label>
                                    <button type="button"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-primary/20 bg-primary/5 text-primary text-[10px] font-black uppercase tracking-widest hover:bg-primary/10"
                                            @click="$dispatch('open-latex-guide')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3v2.25m4.5-2.25v2.25M9 9h6m-8.25 9h10.5A2.25 2.25 0 0019.5 15.75V8.25A2.25 2.25 0 0017.25 6H6.75A2.25 2.25 0 004.5 8.25v7.5A2.25 2.25 0 006.75 18z" />
                                        </svg>
                                        Panduan Rumus
                                    </button>
                                </div>
                                <div class="flex gap-2">
                                    @if($optionCount > 2)
                                    <button type="button" wire:click="removeOption" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 text-[10px] font-black uppercase tracking-widest transition-colors">
                                        - Hapus
                                    </button>
                                    @endif
                                    @if($optionCount < 5)
                                    <button type="button" wire:click="addOption" class="px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 text-[10px] font-black uppercase tracking-widest transition-colors">
                                        + Tambah
                                    </button>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                @foreach(range(0, $optionCount - 1) as $index)
                                    @php $label = chr(65 + $index); @endphp
                                    <div class="group relative flex items-center gap-4 p-2 rounded-2xl transition-colors hover:bg-gray-50 dark:hover:bg-slate-800">
                                        <label class="relative flex items-center justify-center w-12 h-12 cursor-pointer">
                                            <input type="radio" wire:model="questionForm.correct_option" value="{{ $label }}" class="peer sr-only">
                                            <div class="w-10 h-10 rounded-xl bg-gray-200 dark:bg-slate-700 flex items-center justify-center text-text-muted font-black transition-all peer-checked:bg-success peer-checked:text-white peer-checked:shadow-lg peer-checked:scale-110">
                                                {{ $label }}
                                            </div>
                                        </label>
                                        
                                        <div class="flex-1">
                                            <input type="text"
                                                   wire:model.live.debounce.300ms="questionForm.options.{{ $index }}"
                                                   data-latex-enabled="1"
                                                   @focus="window.setLatexActiveInput($event.target); update($event.target.value)"
                                                   @input="update($event.target.value)"
                                                   class="w-full px-5 py-3 bg-white dark:bg-slate-900 border border-border-main dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all font-medium text-sm placeholder-gray-400"
                                                   placeholder="Jawaban opsi {{ $label }}">
                                        </div>
                                    </div>
                                    @error("questionForm.options.{$index}") <p class="pl-16 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                                @endforeach
                                
                                @error('questionForm.correct_option') 
                                    <div class="bg-red-50 text-red-500 px-4 py-3 rounded-xl flex items-center gap-2 border border-red-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        <span class="text-xs font-bold uppercase tracking-wide">Mohon pilih kunci jawaban yang benar</span>
                                    </div>
                                @enderror
                            </div>
                            <div class="rounded-xl border border-blue-100 dark:border-blue-900/40 bg-blue-50/70 dark:bg-slate-800/60 p-4">
                                <p class="text-[10px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-300 mb-2">Pratinjau Rumus</p>
                                <div x-ref="preview" class="text-sm text-slate-700 dark:text-slate-200 min-h-8 break-words"></div>
                            </div>
                        </div>
                    @else
                        <div class="p-6 bg-blue-50/50 dark:bg-blue-900/10 text-blue-700 dark:text-blue-400 rounded-[2rem] border border-blue-100 dark:border-blue-900/30 flex items-start gap-4">
                            <span class="bg-blue-100 dark:bg-blue-900/50 p-2 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            <div>
                                <h4 class="font-bold text-sm uppercase tracking-wide mb-1">Mode Essay Aktif</h4>
                                <p class="text-xs opacity-90 leading-relaxed">Jawaban siswa akan dikoreksi secara manual oleh guru. Kunci jawaban tidak diperlukan pada tahap ini.</p>
                            </div>
                        </div>
                    @endif

                    <!-- Explanation -->
                    <div class="pt-6 border-t border-gray-100 dark:border-slate-800">
                        <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Pembahasan (Opsional)</label>
                        <textarea wire:model="questionForm.explanation" rows="3" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-medium leading-relaxed shadow-inner" placeholder="Jelaskan kenapa jawaban tersebut benar..."></textarea>
                    </div>

                </div>
            </div>

            <!-- Actions Footer -->
            <div class="bg-gray-50 dark:bg-slate-800/50 px-8 py-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100 dark:border-slate-800">
                @if(!$isEdit && method_exists($this, 'openImportFromForm'))
                <button type="button" wire:click="openImportFromForm" class="w-full sm:w-auto px-6 py-3.5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl text-xs font-black text-amber-700 dark:text-amber-300 uppercase tracking-widest hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-all shadow-sm">
                    Import Excel
                </button>
                @endif

                 <button type="button" @click="show = false; {{ (method_exists($this, 'closeModal') ? '$wire.closeModal()' : '$wire.closeQuestionModal()') }}" class="w-full sm:w-auto px-6 py-3.5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs font-black text-gray-500 uppercase tracking-widest hover:bg-gray-50 transition-all shadow-sm">
                    Batal
                </button>
                
                @if(!$isEdit)
                    @if(method_exists($this, 'save'))
                    <button type="button" wire:click="save(true)" class="w-full sm:w-auto px-6 py-3.5 bg-white dark:bg-slate-800 border-2 border-primary/20 text-primary rounded-xl text-xs font-black uppercase tracking-widest hover:bg-primary/5 transition-all shadow-sm">
                        Simpan & Tambah Lagi
                    </button>
                    @elseif(method_exists($this, 'saveAndAddAnother'))
                    <button type="button" wire:click="saveAndAddAnother" class="w-full sm:w-auto px-6 py-3.5 bg-white dark:bg-slate-800 border-2 border-primary/20 text-primary rounded-xl text-xs font-black uppercase tracking-widest hover:bg-primary/5 transition-all shadow-sm">
                        Simpan & Tambah Lagi
                    </button>
                    @endif
                @endif
                
                <button type="button" wire:click="{{ method_exists($this, 'save') ? 'save' : 'saveQuestion' }}" class="w-full sm:w-auto px-8 py-3.5 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg shadow-primary/30 transform active:scale-95">
                    {{ ($isEdit ? 'Perbarui Soal' : 'Simpan Soal') }}
                </button>
            </div>
        </div>
    </div>
</div>
