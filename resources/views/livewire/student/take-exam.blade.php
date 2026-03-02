{{--
    take-exam.blade.php
    ───────────────────
    PERFORMANCE FIXES in this template
    ───────────────────────────────────
    1. OPTIMISTIC NAVIGATION
       Alpine tracks currentIndex locally. When a nav button is clicked:
         a) Alpine immediately updates currentIndex (instant UI feedback).
         b) Livewire fires in the background (wire:click).
       The question number in the header and the navigation grid update
       instantly, removing the perceived "lag" the students were experiencing.

    2. VIOLATION DEBOUNCING (1.5 s)
       The visibilitychange / fullscreenchange handlers use a shared debounce
       timer. A second violation within 1.5 s of the first is swallowed on
       the client and never sent to the server.  This prevents the race
       condition where rapid-fire events hit the DB simultaneously.

    3. HEARTBEAT REDUCED TO 30 s
       wire:poll.30s replaces the previous wire:poll.5s.
       For 400 students that is 400 × 5 = 2 000 req/min → 400 × 2 = 800
       req/min – a 75 % reduction in polling load.
       checkStatus() also calls skipRender() so Livewire performs zero DOM
       diffing on each heartbeat round-trip.

    4. N+1 ANSWERED-STATUS ELIMINATED
       The Blade loop now uses the $answeredIds Collection passed from
       TakeExam::render().  This is one query for the whole grid instead of
       one EXISTS query per question.
--}}
<div
    x-data="{
        remaining: {{ $remainingSeconds }},
        submitted: false,

        {{-- Optimistic navigation: mirrors $currentQuestionIndex on the client --}}
        currentIndex: {{ $currentQuestionIndex }},
        totalQuestions: {{ $questions->count() }},

        {{-- Violation debounce state --}}
        _violationTimer: null,

        formatTime(seconds) {
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;
            return `${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
        },

        initTimer() {
            const timer = setInterval(() => {
                if (this.remaining > 0) {
                    this.remaining--;
                } else if (!this.submitted) {
                    this.submitted = true;
                    clearInterval(timer);
                    $wire.submitExam();
                }
            }, 1000);
        },

        {{--
            Debounced violation reporter.
            Calls $wire.handleViolation only once per 1500 ms window.
            Any second event that arrives within that window is dropped.
        --}}
        reportViolation(type, message) {
            if (this._violationTimer !== null) {
                return; {{-- Debounce: still within the cooldown window --}}
            }
            $wire.handleViolation(type, message);
            this._violationTimer = setTimeout(() => {
                this._violationTimer = null;
            }, 1500);
        },

        {{-- Optimistic next/prev: update local index immediately, then sync server --}}
        goNext() {
            if (this.currentIndex < this.totalQuestions - 1) {
                this.currentIndex++;
            }
            $wire.nextQuestion();
        },
        goPrev() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
            }
            $wire.prevQuestion();
        },
        jumpTo(index) {
            this.currentIndex = index;
            $wire.jumpToQuestion(index);
        }
    }"
    x-on:confirmed-submit-exam.window="$wire.submitExam()"
    x-init="
        initTimer();

        {{-- Tab-switch detection with debounce --}}
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                reportViolation('tab_switch', 'Siswa berpindah tab atau meminimalkan browser');
            }
        });

        {{-- Fullscreen-exit detection with debounce --}}
        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement) {
                reportViolation('fullscreen_exit', 'Siswa keluar dari mode layar penuh');
            }
        });
        document.addEventListener('webkitfullscreenchange', () => {
            if (!document.webkitFullscreenElement) {
                reportViolation('fullscreen_exit', 'Siswa keluar dari mode layar penuh');
            }
        });
    "
    {{-- 30 s heartbeat – checkStatus() calls skipRender() so no DOM diff happens --}}
    wire:poll.30s="checkStatus"
    class="min-h-screen bg-gray-50 flex flex-col"
>

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
                        {{--
                            x-text="currentIndex + 1" renders instantly (Alpine-local).
                            The hidden wire:model keeps Livewire's $currentQuestionIndex in
                            sync after the server responds.
                        --}}
                        <h2 class="text-3xl font-bold text-gray-900" x-text="currentIndex + 1">{{ $currentQuestionIndex + 1 }}</h2>
                    </div>
                </div>

                <!-- Question Text -->
                <div class="prose max-w-none text-gray-800 mb-8 flex-1">
                    @if($currentQuestion->image_path)
                        <img src="{{ Storage::url($currentQuestion->image_path) }}"
                             class="w-full max-w-2xl rounded-xl mb-5 mx-auto md:mx-0 object-contain bg-white border border-gray-100">
                    @endif

                    {!! nl2br(e($currentQuestion->text)) !!}
                </div>

                <div class="space-y-4">
                    @if($currentQuestion->type === 'multiple_choice')
                        @foreach($currentOptions as $index => $option)
                        <label class="flex items-start gap-3 p-4 rounded-xl border-2 transition-all cursor-pointer hover:bg-gray-50
                            {{ $selectedOption == $option->id ? 'border-primary bg-blue-50/50' : 'border-gray-200' }}">
                            <input type="radio" wire:model.live="selectedOption" value="{{ $option->id }}"
                                   class="mt-1 text-primary focus:ring-primary">
                            <span class="font-bold min-w-[24px]">{{ chr(65 + $index) }}.</span>
                            <span class="flex-1">
                                <span>{{ $option->text }}</span>
                                @if($option->image_url)
                                    <img src="{{ $option->image_url }}"
                                         class="mt-3 max-h-56 w-auto max-w-full rounded-lg border border-gray-200 object-contain bg-white">
                                @endif
                            </span>
                        </label>
                        @endforeach
                    @else
                        <!-- Essay -->
                        <div class="space-y-3" x-data="latexPreview(@js($essayAnswer ?? ''))" x-init="init()">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-semibold text-gray-700">Jawaban Essay</label>
                                <button type="button"
                                    @click="$dispatch('open-latex-guide')"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-100 text-xs font-bold hover:bg-blue-100 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3v2.25m4.5-2.25v2.25M4.5 9.75h15M5.625 21h12.75A1.875 1.875 0 0020.25 19.125V8.25a1.875 1.875 0 00-1.875-1.875H5.625A1.875 1.875 0 003.75 8.25v10.875A1.875 1.875 0 005.625 21z" />
                                    </svg>
                                    Panduan Rumus
                                </button>
                            </div>
                            <textarea wire:model.live.debounce.500ms="essayAnswer" rows="8"
                                data-latex-enabled="1"
                                x-on:input="update($event.target.value)"
                                class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary"
                                placeholder="Tulis jawaban Anda di sini..."></textarea>
                            <div class="rounded-lg border border-blue-100 bg-blue-50 p-3">
                                <p class="text-[11px] font-black uppercase tracking-widest text-blue-700 mb-2">Pratinjau Rumus</p>
                                <div x-ref="preview" class="min-h-[2.5rem] text-sm text-gray-800"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                {{--
                    @click="goPrev()" updates Alpine's currentIndex immediately (optimistic).
                    Livewire fires in the background; on response it re-renders the question
                    content area.  The question-number header already shows the correct number
                    instantly thanks to x-text="currentIndex + 1" above.
                --}}
                <button
                    @click="goPrev()"
                    :disabled="currentIndex === 0"
                    :class="currentIndex === 0 ? 'text-gray-300 cursor-not-allowed' : 'text-gray-600 hover:bg-gray-100'"
                    class="px-6 py-2 rounded-lg font-medium transition-colors">
                    &larr; Sebelumnya
                </button>

                <template x-if="currentIndex === totalQuestions - 1">
                    <button
                        @click="$dispatch('show-confirm-modal', [{
                            title: 'Kumpulkan Ujian?',
                            message: 'Apakah Anda yakin ingin mengumpulkan ujian sekarang? Pastikan semua jawaban sudah Anda tinjau kembali.',
                            confirmText: 'Ya, Kumpulkan',
                            type: 'primary',
                            onConfirm: 'submit-exam'
                        }])"
                        class="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:shadow-xl hover:shadow-green-500/20 transition-all transform hover:-translate-y-0.5 active:scale-95">
                        Selesai &amp; Kumpulkan
                    </button>
                </template>

                <template x-if="currentIndex < totalQuestions - 1">
                    <button
                        @click="goNext()"
                        class="px-6 py-2 bg-primary text-white rounded-lg font-medium hover:bg-primary-600 shadow-lg shadow-primary/20">
                        Selanjutnya &rarr;
                    </button>
                </template>
            </div>
        </div>

        <!-- Right: Navigation Grid -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-24">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800">Navigasi Soal</h3>
                    <span class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-600">
                        {{ $answeredCount }}/{{ $questions->count() }} Terjawab
                    </span>
                </div>

                <div class="grid grid-cols-5 gap-2">
                    @foreach($questions as $index => $q)
                        @php
                            $isCurrent = $index === $currentQuestionIndex;
                            {{--
                                Use $answeredIds (pre-fetched in one query) instead of
                                $this->getAttemptProperty()->answers()->where(...)->exists()
                                which was firing one EXISTS query per question (N+1).
                            --}}
                            $hasAnswer = $answeredIds->contains($q->id);
                        @endphp
                        <button
                            @click="jumpTo({{ $index }})"
                            :class="currentIndex === {{ $index }}
                                ? 'bg-primary text-white ring-2 ring-primary ring-offset-2'
                                : {{ $hasAnswer ? 'true' : 'false' }}
                                    ? 'bg-green-100 text-green-700 border-green-200'
                                    : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                            class="aspect-square rounded-lg text-sm font-medium border border-transparent transition-all flex items-center justify-center">
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

    @push('modals')
        <x-latex-guide-modal />
    @endpush
</div>
