@section('title', 'Analisis Butir Soal')

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route($backRoute, $backParam) }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-text-main">Analisis Butir Soal</h2>
                <div class="flex items-center gap-2 text-text-muted text-sm">
                    <span class="font-semibold text-primary">{{ $exam->name }}</span>
                    <span>•</span>
                    <span>{{ $questions->count() }} Soal</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @foreach($questions as $index => $question)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-3">
                <!-- Question Content -->
                <div class="lg:col-span-2 p-6 border-b lg:border-b-0 lg:border-r border-gray-100">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="px-3 py-1 rounded-lg bg-gray-100 text-xs font-bold text-gray-600 uppercase tracking-wide">Soal #{{ $index + 1 }}</span>
                        <span class="px-3 py-1 rounded-lg bg-blue-50 text-xs font-bold text-blue-600 uppercase tracking-wide">{{ $question->type === 'multiple_choice' ? 'Pilihan Ganda' : 'Essay' }}</span>
                    </div>
                    
                    <div class="prose prose-sm max-w-none text-text-main mb-6">
                        {!! $question->text !!}
                    </div>

                    @if($question->type === 'multiple_choice')
                    <div class="space-y-2">
                        @foreach($question->options as $option)
                        <div class="flex items-center justify-between p-3 rounded-xl border {{ $option->is_correct ? 'bg-green-50 border-green-100' : 'bg-white border-gray-100' }}">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $option->is_correct ? 'bg-green-200 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $option->label }}
                                </span>
                                <span class="text-sm {{ $option->is_correct ? 'font-bold text-green-800' : 'text-gray-600' }}">
                                    {{ strip_tags($option->text) }}
                                </span>
                            </div>
                            @if($option->is_correct)
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Stats -->
                <div class="p-6 bg-gray-50/50">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Statistik Jawaban</h4>
                    
                    @php
                        $total = $question->stats->total_answers ?? 0;
                        $correct = $question->stats->correct_count ?? 0;
                        $wrong = $question->stats->wrong_count ?? 0;
                        $correctPercent = $total > 0 ? round(($correct / $total) * 100) : 0;
                        $wrongPercent = $total > 0 ? round(($wrong / $total) * 100) : 0;
                    @endphp

                    <div class="flex items-center gap-2 mb-6">
                        <div class="flex-1 h-4 bg-gray-200 rounded-full overflow-hidden flex">
                            <div class="h-full bg-green-500" style="width: {{ $correctPercent }}%"></div>
                            <div class="h-full bg-red-500" style="width: {{ $wrongPercent }}%"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="text-center p-3 bg-white rounded-xl border border-gray-100 shadow-sm">
                            <div class="text-2xl font-black text-green-500">{{ $correct }}</div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Benar ({{ $correctPercent }}%)</div>
                        </div>
                        <div class="text-center p-3 bg-white rounded-xl border border-gray-100 shadow-sm">
                            <div class="text-2xl font-black text-red-500">{{ $wrong }}</div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Salah ({{ $wrongPercent }}%)</div>
                        </div>
                    </div>

                    @if($question->type === 'multiple_choice')
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Distribusi Pilihan</h4>
                        @foreach($question->options as $option)
                        @php
                            $count = $question->distribution[$option->id] ?? 0;
                            $percent = $total > 0 ? round(($count / $total) * 100) : 0;
                        @endphp
                        <div class="flex items-center gap-3 text-xs">
                            <span class="w-6 font-bold text-gray-600">{{ $option->label }}</span>
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full {{ $option->is_correct ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="w-8 text-right font-bold text-gray-600">{{ $count }}</span>
                            <span class="w-10 text-right text-gray-400">{{ $percent }}%</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
