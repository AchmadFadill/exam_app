<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('teacher.grading.show', ['exam' => 1]) }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
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
                 <div class="text-right flex gap-4">
                     <div class="bg-blue-50 px-4 py-2 rounded-lg border border-blue-100">
                         <p class="text-xs text-text-muted uppercase">Nilai PG (Auto)</p>
                         <p class="text-xl font-bold text-blue-600">{{ $pg_score }}<span class="text-sm font-normal text-gray-400">/{{ $max_pg_score }}</span></p>
                     </div>
                     <div class="bg-amber-50 px-4 py-2 rounded-lg border border-amber-100">
                         <p class="text-xs text-text-muted uppercase">Nilai Essay (Manual)</p>
                         <p class="text-xl font-bold text-amber-600">{{ $current_score - $pg_score }}<span class="text-sm font-normal text-gray-400">/50</span></p>
                     </div>
                     <div class="bg-green-50 px-4 py-2 rounded-lg border border-green-100">
                         <p class="text-xs text-text-muted uppercase">Total Akhir</p>
                         <p class="text-2xl font-bold text-green-600">{{ $current_score }}</p>
                     </div>
                 </div>
             </div>
        </div>
    </div>

    <!-- PG Answers Accordion -->
    <div x-data="{ expanded: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <button @click="expanded = !expanded" class="w-full px-6 py-4 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors">
            <div class="flex items-center gap-3">
                <span class="text-sm font-bold text-text-main uppercase">Rincian Jawaban Pilihan Ganda</span>
                <span class="bg-gray-200 text-gray-700 text-xs px-2 py-0.5 rounded-full">{{ count($pg_answers) }} Soal</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div x-show="expanded" x-collapse style="display: none;">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 border-b border-gray-100 font-medium">
                        <tr>
                            <th class="px-6 py-3 w-10">No</th>
                            <th class="px-6 py-3">Pertanyaan</th>
                            <th class="px-6 py-3">Jawaban Siswa</th>
                            <th class="px-6 py-3">Kunci</th>
                            <th class="px-6 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pg_answers as $pg)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 text-center">{{ $pg['no'] }}</td>
                            <td class="px-6 py-3 text-text-main">{{ $pg['question'] }}</td>
                            <td class="px-6 py-3 {{ $pg['is_correct'] ? 'text-green-600 font-medium' : 'text-red-600 font-medium' }}">{{ $pg['student_answer'] }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $pg['key'] }}</td>
                            <td class="px-6 py-3 text-center">
                                @if($pg['is_correct'])
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        @foreach($answers as $index => $answer)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
             <!-- Question Header -->
             <div class="p-4 border-b border-gray-100 bg-gray-50 flex items-start gap-3">
                 <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold text-sm">
                     {{ $index + 1 }}
                 </span>
                 <div class="flex-1">
                     <p class="text-text-main font-medium">{{ $answer['question'] }}</p>
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

