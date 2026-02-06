@section('title', 'Detail Hasil Siswa')

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route($backRoute, $backParam) }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-text-main">Detail Jawaban Siswa</h2>
                <div class="flex items-center gap-2 text-text-muted text-sm">
                    <span class="font-semibold text-primary">{{ $student->name }}</span>
                    <span>•</span>
                    <span>{{ $exam->name }}</span>
                </div>
            </div>
        </div>

        @if($attempt)
        <div class="flex items-center gap-3">
             <div class="px-4 py-2 bg-white rounded-xl border border-gray-100 shadow-sm text-center">
                <div class="text-[10px] text-text-muted uppercase tracking-widest font-bold">Total Nilai</div>
                <div class="text-xl font-black {{ $attempt->passed ? 'text-green-600' : 'text-red-500' }}">{{ $attempt->total_score }}</div>
            </div>
             <div class="px-4 py-2 bg-white rounded-xl border border-gray-100 shadow-sm text-center">
                <div class="text-[10px] text-text-muted uppercase tracking-widest font-bold">Status</div>
                <div class="text-sm font-bold {{ $attempt->passed ? 'text-green-600' : 'text-red-500' }}">
                    {{ $attempt->passed ? 'LULUS' : 'TIDAK LULUS' }}
                </div>
            </div>
        </div>
        @endif
    </div>

    @if($attempt)
    <!-- Answers List -->
    <div class="max-w-4xl mx-auto space-y-6">
        @foreach($attempt->answers as $index => $answer)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
            <!-- Question Number -->
            <div class="absolute top-0 right-0 px-4 py-2 bg-gray-50 rounded-bl-2xl text-xs font-bold text-gray-500 border-l border-b border-gray-100">
                Soal #{{ $index + 1 }}
            </div>

            <div class="space-y-4">
                <!-- Question Text -->
                <div class="pr-12">
                     <div class="prose prose-sm max-w-none text-text-main">
                        {!! $answer->question->text !!}
                    </div>
                </div>

                <!-- Answer Details -->
                <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100 space-y-3">
                    @if($answer->question->type === 'multiple_choice')
                        <div class="flex items-start gap-3">
                            <div class="w-24 shrink-0 text-xs font-bold text-gray-500 uppercase tracking-wide pt-1">Jawaban Siswa</div>
                            <div class="flex-1">
                                @php
                                    $selectedOption = $answer->question->options->where('id', $answer->selected_option_id)->first();
                                    $isCorrect = $answer->is_correct;
                                @endphp
                                <div class="font-medium {{ $isCorrect ? 'text-green-700' : 'text-red-700' }} flex items-center gap-2">
                                    @if($selectedOption)
                                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold border {{ $isCorrect ? 'bg-green-100 border-green-200 text-green-700' : 'bg-red-100 border-red-200 text-red-700' }}">
                                            {{ $selectedOption->label }}
                                        </span>
                                        <span>{{ strip_tags($selectedOption->text) }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Tidak menjawab</span>
                                    @endif

                                    @if($isCorrect)
                                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Show Correct Answer if Wrong -->
                        @if(!$answer->is_correct)
                        <div class="flex items-start gap-3 pt-3 border-t border-gray-200/50">
                            <div class="w-24 shrink-0 text-xs font-bold text-gray-500 uppercase tracking-wide pt-1">Kunci Jawaban</div>
                            <div class="flex-1">
                                @php
                                    $correctOption = $answer->question->options->where('is_correct', true)->first();
                                @endphp
                                <div class="font-medium text-green-700 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold bg-green-100 border border-green-200 text-green-700">
                                        {{ $correctOption->label ?? '?' }}
                                    </span>
                                    <span>{{ strip_tags($correctOption->text ?? 'Kunci tidak ditemukan') }}</span>
                                </div>
                            </div>
                        </div>
                        @endif

                    @else
                        <!-- Essay -->
                        <div class="space-y-3">
                            <div>
                                <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Jawaban Siswa</div>
                                <div class="p-3 bg-white rounded-lg border border-gray-200 text-sm text-gray-800">
                                    {{ $answer->answer ?? '-' }}
                                </div>
                            </div>
                            
                            @if($answer->teacher_notes)
                            <div class="bg-blue-50/50 rounded-lg p-3 border border-blue-100">
                                <div class="text-xs font-bold text-blue-600 uppercase tracking-wide mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                    Catatan Guru
                                </div>
                                <div class="text-sm text-blue-800">
                                    {{ $answer->teacher_notes }}
                                </div>
                            </div>
                            @endif

                            <div class="flex items-center justify-end gap-2 text-sm pt-2 border-t border-gray-200/50">
                                <span class="text-gray-500">Nilai:</span>
                                <span class="font-bold text-gray-900">{{ $answer->score_awarded }} / {{ $answer->question->score }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <!-- Empty State -->
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <img src="{{ asset('img/not-found.png') }}" alt="Belum Mengerjakan" class="w-98 max-w-md mb-8">
        <h3 class="text-xl font-bold text-slate-800 mb-2">Siswa Belum Mengerjakan</h3>
        <p class="text-slate-500 max-w-md mx-auto">Siswa ini belum memulai atau menyelesaikan ujian, sehingga tidak ada data jawaban yang dapat ditampilkan.</p>
    </div>
    @endif
</div>
