<x-student-layout>
    <x-slot name="title">Detail Hasil Ujian</x-slot>
<div class="container mx-auto px-6 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('student.results') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7"></path></svg>
            Kembali ke Riwayat
        </a>
    </div>

    <!-- Result Header Card -->
    <div class="bg-bg-surface dark:bg-bg-surface rounded-3xl shadow-sm border border-border-subtle dark:border-border-subtle overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-10 text-white">
            <div class="md:flex justify-between items-center">
                <div class="mb-6 md:mb-0">
                    <h2 class="text-3xl font-bold mb-2">{{ $exam->name }}</h2>
                    <p class="text-blue-100 text-lg">{{ $exam->subject->name ?? 'Mata Pelajaran' }}</p>
                    <div class="flex items-center mt-4 text-blue-100 text-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Dikerjakan pada {{ $attempt->started_at->translatedFormat('d F Y, H:i') }}
                    </div>
                </div>
                <div class="bg-bg-surface dark:bg-bg-surface/20 backdrop-blur-md rounded-2xl p-6 text-center border border-white/30">
                    <div class="text-sm font-medium uppercase tracking-wider mb-1">Nilai Akhir</div>
                    <div class="text-6xl font-black">{{ number_format($attempt->percentage ?? 0, 1) }}</div>
                    @if($attempt->passed)
                        <div class="mt-2 text-xs font-semibold px-3 py-1 bg-green-500 rounded-full inline-block">LULUS KKM</div>
                    @else
                        <div class="mt-2 text-xs font-semibold px-3 py-1 bg-red-500 rounded-full inline-block">TIDAK LULUS</div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Stats Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-border-subtle dark:divide-border-subtle border-t border-border-subtle dark:border-border-subtle">
            <div class="p-6 text-center">
                <div class="text-sm font-medium text-text-muted mb-1 leading-tight">Total Soal</div>
                <div class="text-2xl font-bold text-text-main">{{ $exam->questions->count() }}</div>
            </div>
            <div class="p-6 text-center">
                <div class="text-sm font-medium text-green-600 mb-1 leading-tight">Benar</div>
                <div class="text-2xl font-bold text-green-600">{{ $attempt->answers->where('is_correct', true)->count() }}</div>
            </div>
            <div class="p-6 text-center">
                <div class="text-sm font-medium text-red-600 mb-1 leading-tight">Salah</div>
                <div class="text-2xl font-bold text-red-600">{{ $attempt->answers->where('is_correct', false)->count() }}</div>
            </div>
            <div class="p-6 text-center">
                <div class="text-sm font-medium text-text-muted mb-1 leading-tight">Score Total</div>
                <div class="text-2xl font-bold text-text-muted">{{ $attempt->total_score }}</div>
            </div>
        </div>
    </div>

    <!-- Discussion Section -->
    <div class="mb-6 flex items-center justify-between">
        <h4 class="text-text-main text-xl font-bold">Pembahasan Jawaban</h4>
        <!-- <div class="text-sm text-text-muted">Menampilkan semua soal</div> -->
    </div>

    <div class="space-y-6">
        @foreach($exam->questions as $index => $question)
            @php
                $studentAnswer = $attempt->answers->where('question_id', $question->id)->first();
                $isCorrect = $studentAnswer && $studentAnswer->is_correct;
                $userOptionId = $studentAnswer->selected_option_id ?? null;
                $correctOption = $question->options->where('is_correct', true)->first();
                $userOption = $question->options->where('id', $userOptionId)->first();
            @endphp
        <div class="bg-bg-surface dark:bg-bg-surface rounded-2xl shadow-sm border border-border-subtle dark:border-border-subtle overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-bold text-text-muted">SOAL NO. {{ $index + 1 }}</span>
                    @if($isCorrect)
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        BENAR
                    </span>
                    @else
                    <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        SALAH
                    </span>
                    @endif
                </div>
                <div class="prose max-w-none text-text-main mb-4">
                    {!! $question->text !!}
                </div>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="text-sm">
                        <div class="font-semibold text-text-muted mb-2">Jawaban Kamu:</div>
                        <div class="p-3 {{ $isCorrect ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' }} border rounded-xl font-medium">
                             {{ $userOption->text ?? 'Tidak Menjawab / Essay' }}
                        </div>
                    </div>
                     <div class="text-sm">
                        <div class="font-semibold text-text-muted mb-2">Kunci Jawaban:</div>
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-blue-700 font-medium">
                             {{ $correctOption->text ?? '-' }}
                        </div>
                    </div>
                </div>

                @if($question->explanation)
                <!-- Teacher Explanation -->
                <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <div class="flex items-center mb-2">
                         <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-2">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                         </div>
                         <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Catatan Dari Guru</span>
                    </div>
                    <p class="text-sm text-gray-600">
                        {{ $question->explanation }}
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

</div>
</x-student-layout>
