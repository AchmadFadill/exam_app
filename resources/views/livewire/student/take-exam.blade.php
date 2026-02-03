<div x-data="{ 
    remaining: {{ $remainingSeconds }},
    submitted: false,
    formatTime(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    },
    initTimer() {
        const timer = setInterval(() => {
            if (this.remaining > 0) {
                this.remaining--;
            } else if (!this.submitted) {
                // Time's up! Trigger submission once
                this.submitted = true;
                clearInterval(timer);
                console.log('Time expired - auto-submitting exam...');
                $wire.submitExam();
            }
        }, 1000);
    }
}" x-init="initTimer()" class="min-h-screen bg-gray-50 flex flex-col">
    
    <!-- Time's Up Overlay -->
    <div x-show="remaining <= 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center"
         style="display: none;">
        <div class="bg-white rounded-2xl p-8 text-center max-w-md shadow-2xl">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Waktu Habis!</h3>
            <p class="text-gray-600 mb-4">Ujian Anda telah berakhir dan sedang dikumpulkan...</p>
            <div class="flex items-center justify-center gap-2">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-primary"></div>
                <span class="text-sm text-gray-500">Menyimpan jawaban...</span>
            </div>
        </div>
    </div>
    
    <!-- Top Bar: Timer & Status -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
            <h1 class="font-bold text-gray-800 truncate">{{ $exam->name }}</h1>
            
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg font-mono font-bold text-lg" 
                     :class="{'bg-red-50 text-red-600': remaining < 300}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="formatTime(remaining)"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 max-w-7xl mx-auto w-full p-4 grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Left: Question Area -->
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 min-h-[500px] flex flex-col">
                <!-- Question Header -->
                <div class="flex justify-between items-start mb-6 pb-4 border-b border-gray-100">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Soal No.</span>
                        <h2 class="text-3xl font-bold text-gray-900">{{ $currentQuestionIndex + 1 }}</h2>
                    </div>
                </div>

                <!-- Question Text -->
                <div class="prose max-w-none text-gray-800 mb-8 flex-1">
                    @if($currentQuestion->image_path)
                        <img src="{{ Storage::url($currentQuestion->image_path) }}" class="max-w-md rounded-lg mb-4 mx-auto md:mx-0">
                    @endif
                    
                    {!! nl2br(e($currentQuestion->text)) !!}
                </div>

                <!-- Answer Options -->
                <div class="space-y-4">
                    @if($currentQuestion->type === 'multiple_choice')
                        @foreach($currentQuestion->options as $option)
                        <label class="flex items-start gap-3 p-4 rounded-xl border-2 transition-all cursor-pointer hover:bg-gray-50
                            {{ $selectedOption === $option->label ? 'border-primary bg-blue-50/50' : 'border-gray-200' }}">
                            <input type="radio" wire:model.live="selectedOption" value="{{ $option->label }}" class="mt-1 text-primary focus:ring-primary">
                            <span class="font-bold min-w-[24px]">{{ $option->label }}.</span>
                            <span class="flex-1">{{ $option->text }}</span>
                        </label>
                        @endforeach
                    @else
                        <!-- Essay -->
                        <textarea wire:model.live.debounce.500ms="essayAnswer" rows="8" 
                            class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary"
                            placeholder="Tulis jawaban Anda di sini..."></textarea>
                    @endif
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <button wire:click="prevQuestion" 
                    class="px-6 py-2 rounded-lg font-medium transition-colors {{ $currentQuestionIndex === 0 ? 'text-gray-300 cursor-not-allowed' : 'text-gray-600 hover:bg-gray-100' }}"
                    {{ $currentQuestionIndex === 0 ? 'disabled' : '' }}>
                    &larr; Sebelumnya
                </button>
                
                @if($currentQuestionIndex === $questions->count() - 1)
                    <button wire:click="submitExam" 
                        onclick="confirm('Apakah Anda yakin ingin mengumpulkan ujian? Pastikan semua jawaban sudah terisi.') || event.stopImmediatePropagation()"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 shadow-lg shadow-green-600/20">
                        Selesai & Kumpulkan
                    </button>
                @else
                    <button wire:click="nextQuestion" class="px-6 py-2 bg-primary text-white rounded-lg font-medium hover:bg-primary-600 shadow-lg shadow-primary/20">
                        Selanjutnya &rarr;
                    </button>
                @endif
            </div>
        </div>

        <!-- Right: Navigation Grid -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-24">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800">Navigasi Soal</h3>
                    <span class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-600">{{ $answeredCount }}/{{ $questions->count() }} Terjawab</span>
                </div>
                
                <div class="grid grid-cols-5 gap-2">
                    @foreach($questions as $index => $q)
                        @php
                            $isCurrent = $index === $currentQuestionIndex;
                            $hasAnswer = $this->getAttemptProperty()->answers()->where('question_id', $q->id)->exists();
                            $btnClass = $isCurrent ? 'bg-primary text-white ring-2 ring-primary ring-offset-2' : 
                                       ($hasAnswer ? 'bg-green-100 text-green-700 border-green-200' : 'bg-gray-50 text-gray-600 hover:bg-gray-100');
                        @endphp
                        <button wire:click="jumpToQuestion({{ $index }})" 
                            class="aspect-square rounded-lg text-sm font-medium border border-transparent transition-all flex items-center justify-center {{ $btnClass }}">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-6 pt-6 border-t border-gray-100">
                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-500">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-green-100 border border-green-200 rounded"></span>
                            <span>Terjawab</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-primary rounded"></span>
                            <span>Sekarang</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-gray-50 rounded"></span>
                            <span>Belum</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
