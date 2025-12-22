<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('teacher.grading.index') }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div class="flex-1">
             <div class="flex justify-between items-start">
                 <div>
                    <h2 class="font-bold text-2xl text-text-main">Koreksi Jawaban</h2>
                    <div class="flex items-center gap-2 text-text-muted text-sm mt-1">
                        <span class="font-semibold text-text-main">{{ $student_name }}</span>
                        <span>•</span>
                        <span>{{ $grade }}</span>
                    </div>
                 </div>
                 <div class="text-right">
                     <p class="text-xs text-text-muted uppercase">Total Nilai Sementara</p>
                     <p class="text-3xl font-bold text-primary">{{ $current_score }}</p>
                 </div>
             </div>
        </div>
    </div>

             
             <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                 <!-- Student Answer -->
                 <div class="space-y-2">
                     <label class="block text-xs font-semibold text-text-muted uppercase">Jawaban Siswa</label>
                     <div class="p-4 bg-blue-50 rounded-lg text-text-main text-sm italic leading-relaxed border border-blue-100">
                         "{{ $answer['student_answer'] }}"
                     </div>
                 </div>

                 <!-- Teacher Guide & Scoring -->
                 <div class="space-y-4">
                     <div>
                         <label class="block text-xs font-semibold text-text-muted uppercase mb-1">Kunci Jawaban / Panduan</label>
                         <div class="p-3 bg-green-50 rounded-lg text-green-800 text-xs border border-green-100">
                             {{ $answer['key'] }}
                         </div>
                     </div>
                     
                     <div class="grid grid-cols-2 gap-4">
                         <div>
                             <label class="block text-xs font-semibold text-text-muted uppercase mb-1">Nilai (Max {{ $answer['max_score'] }})</label>
                             <input type="number" wire:model="answers.{{ $index }}.score" max="{{ $answer['max_score'] }}" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main">
                         </div>
                         <div class="flex items-end">
                             <span class="text-sm text-slate-400 pb-2">/ {{ $answer['max_score'] }} Poin</span>
                         </div>
                     </div>

                     <div>
                         <label class="block text-xs font-semibold text-text-muted uppercase mb-1">Feedback Guru</label>
                         <textarea wire:model="answers.{{ $index }}.feedback" rows="2" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main text-sm" placeholder="Berikan catatan untuk siswa..."></textarea>
                     </div>
                 </div>
             </div>
        </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between pb-10">
        <button class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-text-main hover:bg-gray-50 font-medium transition-colors">
            &larr; Siswa Sebelumnya
        </button>
        <button class="px-6 py-2 bg-primary hover:bg-blue-700 text-white rounded-lg font-medium transition-colors shadow-sm">
            Simpan & Lanjut Siswa Berikutnya &rarr;
        </button>
    </div>
</div>

