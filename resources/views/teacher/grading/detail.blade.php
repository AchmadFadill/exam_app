<div class="relative pb-32">
    <!-- Sticky Header -->
    <div class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-gray-200 -mx-4 px-4 py-4 mb-8 sm:-mx-8 sm:px-8 shadow-sm transition-all">
        <div class="flex items-center justify-between gap-4 max-w-7xl mx-auto">
            <div class="flex items-center gap-4">
                <a href="{{ route('teacher.grading.show', ['exam' => $exam->id]) }}" class="p-2 rounded-xl hover:bg-gray-100 text-text-muted transition-colors border border-transparent hover:border-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-black text-xl text-text-main tracking-tight leading-none">Koreksi Jawaban</h2>
                    <div class="flex items-center gap-2 text-xs font-bold text-text-muted uppercase tracking-widest mt-1.5 opacity-60">
                        <span>{{ $student_name }}</span>
                        <span>&bull;</span>
                        <span>{{ $grade }}</span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="hidden md:block text-right mr-2">
                    <p class="text-[10px] font-black text-text-muted uppercase tracking-widest opacity-50">Progres Nilai</p>
                </div>
                <div class="flex gap-2">
                    <div class="bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 text-center min-w-[80px]">
                        <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-0.5">PG (Auto)</p>
                        <p class="text-sm font-black text-blue-600">{{ $pgScore }}<span class="text-[10px] font-bold text-blue-300">/{{ $maxPgScore }}</span></p>
                    </div>
                    <div class="bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-100 text-center min-w-[80px]">
                        <p class="text-[9px] font-black text-amber-400 uppercase tracking-widest mb-0.5">Essay</p>
                        <p class="text-sm font-black text-amber-600">{{ $this->currentTotalScore - $pgScore }}</p>
                    </div>
                    <div class="bg-green-50 px-4 py-1.5 rounded-lg border border-green-100 text-center min-w-[90px] shadow-sm">
                        <p class="text-[9px] font-black text-green-400 uppercase tracking-widest mb-0.5">Total</p>
                        <p class="text-lg font-black text-green-600 leading-none">{{ $this->currentTotalScore }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto space-y-10">
        <!-- PG Answers Accordion -->
        <div x-data="{ expanded: false }" class="bg-white rounded-[2rem] shadow-sm border border-gray-200 overflow-hidden group hover:border-blue-200 transition-colors">
            <button @click="expanded = !expanded" class="w-full px-8 py-6 flex items-center justify-between bg-white hover:bg-gray-50 transition-colors cursor-pointer">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="text-left">
                        <h3 class="text-sm font-black text-text-main uppercase tracking-widest">Rincian Jawaban Pilihan Ganda</h3>
                        <p class="text-[10px] font-bold text-text-muted mt-1">Klik untuk melihat detail {{ count($pgAnswers) }} soal otomatis</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="bg-gray-100 text-text-muted text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">{{ count($pgAnswers) }} Soal</span>
                    <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-300" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </button>
            
            <div x-show="expanded" x-collapse>
                <div class="overflow-x-auto border-t border-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="py-4 pl-8 pr-4 text-xs font-black text-text-muted uppercase tracking-widest w-16 text-center">No</th>
                                <th class="py-4 px-4 text-xs font-black text-text-muted uppercase tracking-widest">Pertanyaan</th>
                                <th class="py-4 px-4 text-xs font-black text-text-muted uppercase tracking-widest w-64">Jawaban Siswa</th>
                                <th class="py-4 px-4 text-xs font-black text-text-muted uppercase tracking-widest w-48">Kunci</th>
                                <th class="py-4 pl-4 pr-8 text-xs font-black text-text-muted uppercase tracking-widest w-24 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($pgAnswers as $pg)
                            <tr class="hover:bg-gray-50/80 transition-colors {{ $pg['is_correct'] ? '' : 'bg-red-50/30' }}">
                                <td class="py-4 pl-8 pr-4 text-center font-bold text-text-muted opacity-60">{{ $pg['no'] }}</td>
                                <td class="py-4 px-4 text-sm font-medium text-text-main leading-relaxed min-w-[300px]">{!! $pg['question'] !!}</td>
                                <td class="py-4 px-4">
                                    <span class="text-xs font-black uppercase tracking-widest {{ $pg['is_correct'] ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $pg['student_answer'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-xs font-bold text-text-muted uppercase tracking-widest opacity-60">{!! $pg['key'] !!}</td>
                                <td class="py-4 pl-4 pr-8 text-center">
                                    <div class="flex justify-center">
                                        @if($pg['is_correct'])
                                            <div class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-green-100 text-green-600 shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                        @else
                                            <div class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-red-100 text-red-600 shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div class="flex items-center gap-4">
                <div class="h-px bg-gray-200 flex-1"></div>
                <span class="text-xs font-black text-text-muted uppercase tracking-[0.2em] bg-bg-app px-4">Penilaian Essay</span>
                <div class="h-px bg-gray-200 flex-1"></div>
            </div>

            @foreach($essayGrades as $answerId => $data)
            <div class="bg-white rounded-[2.5rem] shadow-lg shadow-black/5 border border-gray-100 overflow-hidden relative group hover:border-primary/30 transition-all duration-300">
                 <!-- Number Badge -->
                 <div class="absolute top-0 left-0 bg-gray-50 border-r border-b border-gray-100 rounded-br-[2rem] px-6 py-4 z-10">
                     <span class="text-xl font-black text-gray-300 group-hover:text-primary transition-colors italic">#{{ $loop->iteration }}</span>
                 </div>

                 <div class="p-8 pt-20 sm:pt-8 sm:pl-24">
                     <h4 class="text-lg font-bold text-text-main mb-8 leading-relaxed">{!! $data['question'] !!}</h4>
                     
                     <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                         <!-- Student Answer Column -->
                         <div class="space-y-3">
                             <label class="flex items-center gap-2 text-[10px] font-black text-text-muted uppercase tracking-widest opacity-70">
                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                 Jawaban Siswa
                             </label>
                             <div class="relative group/paper">
                                <div class="absolute inset-0 bg-amber-50 rounded-2xl transform rotate-1 group-hover/paper:rotate-2 transition-transform"></div>
                                <div class="relative bg-white border border-amber-100 rounded-2xl p-6 shadow-sm min-h-[160px]">
                                    <!-- Lined paper effect -->
                                    <div class="absolute inset-x-6 top-0 bottom-0 pointer-events-none opacity-10" style="background-image: linear-gradient(#000 1px, transparent 1px); background-size: 100% 2rem; background-position: 0 1.5rem;"></div>
                                    <p class="relative text-sm text-slate-700 font-medium leading-8 font-serif italic">
                                        "{{ $data['student_answer'] }}"
                                    </p>
                                </div>
                             </div>
                         </div>

                         <!-- Grading Column -->
                         <div class="bg-gray-50/50 rounded-3xl p-6 border border-gray-100 space-y-6">
                             <div>
                                 <label class="flex items-center gap-2 text-[10px] font-black text-text-muted uppercase tracking-widest opacity-70 mb-3">
                                     <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                     Kunci Jawaban / Penjelasan
                                 </label>
                                 <div class="p-4 bg-green-50/50 rounded-2xl text-green-800 text-xs font-medium border border-green-100/50 leading-relaxed shadow-inner">
                                     {!! $data['key'] !!}
                                 </div>
                             </div>
                             
                             <div class="h-px bg-gray-200"></div>

                             <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                 <div x-data="{ score: @entangle('essayGrades.'.$answerId.'.score').live }">
                                     <label class="flex items-center justify-between text-[10px] font-black text-text-muted uppercase tracking-widest opacity-70 mb-2">
                                         <span>Nilai</span>
                                         <span class="text-primary">Max {{ $data['max_score'] }}</span>
                                     </label>
                                     <div class="flex items-center gap-2">
                                         <input type="number" x-model="score" max="{{ $data['max_score'] }}" class="w-20 px-3 py-2 text-center bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary font-black text-lg shadow-inner outline-none transition-all">
                                         <div class="flex flex-1 gap-1">
                                             <button type="button" @click="score = 0" class="flex-1 py-2 bg-red-50 hover:bg-red-100 text-red-500 rounded-lg text-[10px] font-black uppercase transition-colors" title="Nol">0</button>
                                             <button type="button" @click="score = {{ ceil($data['max_score'] / 2) }}" class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-lg text-[10px] font-black uppercase transition-colors" title="Setengah">½</button>
                                             <button type="button" @click="score = {{ $data['max_score'] }}" class="flex-1 py-2 bg-green-50 hover:bg-green-100 text-green-600 rounded-lg text-[10px] font-black uppercase transition-colors" title="Maksimal">Max</button>
                                         </div>
                                     </div>
                                 </div>

                                 <div>
                                     <label class="block text-[10px] font-black text-text-muted uppercase tracking-widest opacity-70 mb-2">Feedback</label>
                                     <textarea wire:model="essayGrades.{{ $answerId }}.feedback" rows="1" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary text-text-main text-xs font-medium shadow-inner outline-none transition-all resize-none focus:h-24" placeholder="Tulis catatan..."></textarea>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Fixed Bottom Actions -->
    <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/90 backdrop-blur-xl border-t border-gray-200 p-4 lg:px-8 shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.1)]">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-6">
            <x-button variant="soft" href="{{ route('teacher.grading.show', ['exam' => $exam->id]) }}" class="px-6 py-3 rounded-xl text-xs font-black bg-gray-100 hover:bg-gray-200 text-text-muted hover:text-text-main border-none shadow-none">
                &larr; KEMBALI 
            </x-button>
            
            <div class="flex items-center gap-4">
                <div class="hidden sm:block text-right">
                    <p class="text-[10px] font-bold text-text-muted uppercase tracking-widest">Total Nilai Akhir</p>
                    <p class="text-xl font-black text-primary">{{ $this->currentTotalScore }}</p>
                </div>
                <!-- Save Button -->
                <x-button wire:click="finishGrading" variant="primary" class="px-8 py-3.5 rounded-xl text-xs font-black shadow-xl shadow-primary/20 hover:scale-105 active:scale-95 transition-all">
                    SIMPAN & SELESAI
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </x-button>
            </div>
        </div>
    </div>
</div>

