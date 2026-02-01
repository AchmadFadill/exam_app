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
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <x-table.thead>
                        <x-table.tr>
                            <x-table.th class="w-16 text-center">No</x-table.th>
                            <x-table.th>Pertanyaan</x-table.th>
                            <x-table.th>Jawaban Siswa</x-table.th>
                            <x-table.th>Kunci</x-table.th>
                            <x-table.th class="text-center">Status</x-table.th>
                        </x-table.tr>
                    </x-table.thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                        @foreach($pg_answers as $pg)
                        <x-table.tr>
                            <x-table.td class="text-center font-bold text-text-muted italic">{{ $pg['no'] }}</x-table.td>
                            <x-table.td class="text-text-main font-medium min-w-[300px]">{{ $pg['question'] }}</x-table.td>
                            <x-table.td class="{{ $pg['is_correct'] ? 'text-green-600 font-black' : 'text-red-600 font-black' }} uppercase tracking-tight">{{ $pg['student_answer'] }}</x-table.td>
                            <x-table.td class="text-text-muted font-bold uppercase">{{ $pg['key'] }}</x-table.td>
                            <x-table.td class="text-center">
                                <div class="flex justify-center">
                                    @if($pg['is_correct'])
                                        <div class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-green-500/10 text-green-600 border border-green-500/20 shadow-inner">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                    @else
                                        <div class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-red-500/10 text-red-600 border border-red-500/20 shadow-inner">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </div>
                                    @endif
                                </div>
                            </x-table.td>
                        </x-table.tr>
                        @endforeach
                    </tbody>
                </table>
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

