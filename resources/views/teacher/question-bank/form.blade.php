@section('title', $questionId ? 'Edit Soal' : 'Buat Soal Baru')

<div class="max-w-4xl mx-auto space-y-6">
    <div class="mb-12 flex items-center gap-6">
        <a href="{{ route('teacher.question-bank.index') }}" class="group p-4 bg-bg-surface dark:bg-slate-800 rounded-2xl border border-border-main dark:border-slate-700 text-text-muted hover:text-primary hover:border-primary transition-all shadow-sm">
            <svg class="w-6 h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h2 class="text-4xl font-black text-text-main tracking-tight uppercase">
                {{ $questionId ? 'Refine Item' : 'Deploy Item' }}
            </h2>
            <p class="text-[10px] font-black text-text-muted mt-2 uppercase tracking-[0.2em] opacity-60">Knowledge Authoring System</p>
        </div>
    </div>

    <div class="bg-bg-surface dark:bg-slate-900 rounded-[2.5rem] shadow-2xl shadow-black/5 border border-white/5 overflow-hidden">
        <form wire:submit.prevent="save" class="p-10 space-y-10">
            
            <!-- Type & Subject Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70">Disiplin Akademik</label>
                    <select wire:model="subject" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold appearance-none bg-no-repeat bg-[right_1.5rem_center] bg-[length:1em_1em]" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22currentColor%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222.5%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E')">
                        <option value="">Pilih Domain Ilmu</option>
                        <option value="Matematika">Matematika</option>
                        <option value="Biologi">Biologi</option>
                        <option value="Sejarah">Sejarah</option>
                        <option value="Geografi">Geografi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70">Format Penilaian</label>
                    <select wire:model.live="type" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold appearance-none bg-no-repeat bg-[right_1.5rem_center] bg-[length:1em_1em]" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22currentColor%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222.5%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E')">
                        <option value="multiple_choice">Tertutup (Pilihan Ganda)</option>
                        <option value="essay">Terbuka (Analisis/Essay)</option>
                    </select>
                </div>
            </div>

            <div class="border-t border-border-subtle dark:border-slate-800 pt-10">
                <label class="block text-xs font-black text-text-main mb-4 uppercase tracking-widest opacity-70">Naskah Pertanyaan</label>
                <textarea wire:model="question_text" rows="5" class="w-full px-6 py-6 bg-gray-100/50 dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold leading-relaxed shadow-inner" placeholder="Artikulasikan problem atau pertanyaan di sini..."></textarea>
                
                <div class="mt-8">
                    <label class="block text-xs font-black text-text-main mb-4 uppercase tracking-widest opacity-70">Ilustrasi / Media Pendukung (Opsional)</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-40 border-2 border-border-main dark:border-slate-700 border-dashed rounded-[2rem] cursor-pointer bg-gray-50/50 dark:bg-slate-900 hover:bg-gray-100 dark:hover:bg-slate-800/50 transition-all group/media">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-4 text-text-muted group-hover/media:text-primary transition-colors" fill="none" viewBox="0 0 20 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.017 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                </svg>
                                <p class="mb-2 text-xs text-text-muted font-bold uppercase tracking-widest"><span class="text-primary">Transmit Media</span> or Drag-and-Drop</p>
                                <p class="text-[10px] text-text-muted opacity-40 uppercase tracking-widest">SVG, PNG, JPG (Max. 5MB)</p>
                            </div>
                            <input id="dropzone-file" type="file" class="hidden" wire:model="question_image" />
                        </label>
                    </div>
                </div>
            </div>

            @if($type === 'multiple_choice')
            <div class="border-t border-border-subtle dark:border-slate-800 pt-10 space-y-6">
                <label class="block text-xs font-black text-text-main mb-2 uppercase tracking-widest opacity-70 text-center">Opsi Respon Terpimpin</label>
                <div class="space-y-4">
                    @foreach($options as $index => $option)
                    <div class="flex items-center gap-5 group/option transition-all hover:-translate-x-1">
                        <div class="shrink-0">
                             <input type="radio" name="correct_answer" wire:click="setCorrectAnswer({{ $index }})" {{ $correct_answer_index === $index ? 'checked' : '' }} class="w-8 h-8 text-primary bg-bg-surface dark:bg-slate-800 border-2 border-border-main dark:border-slate-700 focus:ring-4 focus:ring-primary/20 transition-all shadow-inner">
                        </div>
                        <div class="flex-1">
                            <div class="flex rounded-2xl shadow-sm border border-border-main dark:border-slate-700 overflow-hidden focus-within:ring-4 focus-within:ring-primary/10 transition-all">
                                <span class="inline-flex items-center px-6 bg-gray-100/50 dark:bg-slate-800 text-text-muted font-black text-sm border-r border-border-main dark:border-slate-700">
                                    {{ chr(65 + $index) }}
                                </span>
                                <input type="text" wire:model="options.{{ $index }}.text" class="flex-1 px-6 py-4 bg-bg-surface dark:bg-slate-900 font-bold text-sm outline-none" placeholder="Isi materi pilihan {{ chr(65 + $index) }}">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div class="border-t border-gray-100 pt-6">
                <label class="block text-sm font-medium text-text-main mb-2">Pembahasan (Opsional)</label>
                <textarea wire:model="explanation" rows="3" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main" placeholder="Jelaskan kenapa jawaban tersebut benar..."></textarea>
            </div>

            @elseif($type === 'essay')
            <div class="border-t border-gray-100 pt-6">
                <label class="block text-sm font-medium text-text-main mb-2">Kunci Jawaban / Panduan Penilaian</label>
                <textarea wire:model="answer_key" rows="5" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main" placeholder="Tuliskan jawaban yang diharapkan atau poin-poin penting..."></textarea>
            </div>
            @endif



            <div class="flex items-center justify-end gap-5 pt-10 border-t border-border-subtle dark:border-slate-800">
                <a href="{{ route('teacher.question-bank.index') }}" class="px-8 py-4 bg-bg-surface dark:bg-slate-800 border border-border-main dark:border-slate-700 rounded-2xl text-[10px] font-black uppercase tracking-widest text-text-main hover:bg-gray-100 transition-all">Batalkan</a>
                <button type="submit" class="px-10 py-4 bg-primary hover:bg-blue-700 text-white rounded-[2rem] text-[10px] font-black uppercase tracking-widest transition-all shadow-xl shadow-primary/20">Sinkronisasi Database</button>
            </div>
        </form>
    </div>
</div>
</div>
