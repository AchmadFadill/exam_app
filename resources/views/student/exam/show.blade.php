<x-exam-layout title="Ujian Matematika">
    <x-slot name="header_actions">
        <!-- Header Actions: User Info & Timer -->
        <div class="flex items-center space-x-2 sm:space-x-4" x-data>
            <div class="hidden lg:flex flex-col items-end mr-4">
                <span class="text-sm font-semibold text-gray-700">{{ Auth::user()->name }}</span>
                <span class="text-xs text-gray-500">NIS: {{ Auth::user()->student->nis ?? '-' }}</span>
            </div>
            
            <!-- Timer Badge -->
            <div class="px-2 sm:px-3 py-1 bg-blue-50 text-blue-700 rounded-lg border border-blue-100 font-mono font-bold text-sm sm:text-lg flex items-center shadow-sm">
                <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-text="$store.exam.formattedTime" :class="{'text-red-600 animate-pulse': $store.exam.timeLeft < 300}"></span>
            </div>
        </div>
    </x-slot>

    <!-- Main Exam UI -->
    <div x-data="examData()" x-init="initExam()" class="container mx-auto max-w-7xl px-2 sm:px-4 lg:px-8 py-4 sm:py-8 h-full select-none">
        @if($exam->enable_tab_tolerance)
        <div class="pointer-events-none fixed inset-0 z-10 overflow-hidden opacity-[0.05] print:hidden" aria-hidden="true">
            <div class="absolute inset-0 [background-size:280px_160px] [background-image:repeating-linear-gradient(-28deg,transparent,transparent_80px,rgba(17,24,39,.12)_80px,rgba(17,24,39,.12)_82px)]"></div>
            <div class="absolute inset-0 flex flex-wrap content-start">
                @for($i = 0; $i < 24; $i++)
                    <div class="w-1/3 px-8 py-6 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-900/70 rotate-[-18deg]">
                        {{ $studentName }} • {{ $studentNis }}
                    </div>
                @endfor
            </div>
        </div>
        @endif
        <div x-cloak x-show="isOffline || saveErrorMessage || lastHeartbeatFailed" class="mb-4 rounded-lg border px-4 py-3 text-sm"
            :class="isOffline ? 'bg-amber-50 border-amber-300 text-amber-800' : 'bg-red-50 border-red-300 text-red-800'">
            <template x-if="isOffline">
                <div>Koneksi internet terputus. Jawaban akan disimpan otomatis saat koneksi kembali.</div>
            </template>
            <template x-if="!isOffline && saveErrorMessage">
                <div x-text="saveErrorMessage"></div>
            </template>
            <template x-if="!isOffline && !saveErrorMessage && lastHeartbeatFailed">
                <div>Koneksi ke server tidak stabil. Sistem sedang mencoba terhubung ulang.</div>
            </template>
            <template x-if="Object.keys(pendingSaves).length > 0">
                <div class="mt-1 font-semibold" x-text="`Jawaban tertunda: ${Object.keys(pendingSaves).length}`"></div>
            </template>
        </div>

        <!-- Fullscreen helper (best-effort: browsers may block fullscreen without a user gesture) -->
        <div x-cloak x-show="needsFullscreen && Alpine.store('exam').isActive && !showStartOverlay && !showWarningModal && !showFinishModal && !isBlocked"
            class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 flex items-start justify-between gap-3">
            <div>
                <div class="font-semibold">Mode layar penuh diperlukan</div>
                <div class="text-blue-800">Jika setelah refresh layar penuh tidak aktif, klik tombol di kanan untuk masuk kembali.</div>
            </div>
            <button type="button" @click="tryEnterFullscreen()"
                class="shrink-0 inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-white font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Masuk Layar Penuh
            </button>
        </div>
        
        <div class="flex flex-col lg:flex-row gap-4 lg:gap-6 min-h-[calc(100vh-8rem)]">
            
            <!-- Question Area (Left) -->
            <div class="flex-1 flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden min-h-[500px] lg:h-full">
                <!-- Question Header -->
                <div class="px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h2 class="text-base sm:text-lg font-bold text-gray-800">
                        Soal No. <span x-text="currentQuestion + 1"></span>
                    </h2>
                    <div class="flex items-center space-x-2">
                        <span x-show="questions[currentQuestion].type === 'multiple_choice'" class="text-xs font-medium bg-gray-200 text-gray-600 px-2 py-1 rounded">PG</span>
                        <span x-show="questions[currentQuestion].type === 'essay'" class="text-xs font-medium bg-amber-100 text-amber-700 px-2 py-1 rounded">Essay</span>
                    </div>
                </div>

                <!-- Question Content -->
                <div class="flex-1 overflow-y-auto p-3 sm:p-6" x-ref="mathRoot">
                    <div class="prose max-w-none text-gray-800 text-base sm:text-lg mb-6 sm:mb-8">
                        <!-- Image Rendering -->
                        <template x-if="questions[currentQuestion].image_path">
                            <img :src="questions[currentQuestion].image_path" class="w-full max-w-2xl rounded-xl mb-5 mx-auto md:mx-0 shadow-sm border border-gray-100 object-contain bg-white">
                        </template>
                        
                        <div x-html="questions[currentQuestion].text"></div>
                    </div>

                    <!-- Answer Input -->
                    <div class="space-y-3 sm:space-y-4">
                        <template x-if="questions[currentQuestion].type === 'multiple_choice'">
                            <div class="space-y-3 sm:space-y-4">
                                <template x-for="(option, index) in questions[currentQuestion].options" :key="index">
                                    <label class="flex items-start p-3 sm:p-4 rounded-lg border-2 cursor-pointer transition-all duration-200 group hover:bg-gray-50 active:scale-[0.98]"
                                        :class="{
                                            'border-blue-600 bg-blue-50': answers[questions[currentQuestion].id] === option.id,
                                            'border-gray-200': answers[questions[currentQuestion].id] !== option.id
                                        }">
                                        <input type="radio"
                                            :name="'question_' + questions[currentQuestion].id"
                                            :value="option.id"
                                            x-model="answers[questions[currentQuestion].id]"
                                            @change="saveProgress(questions[currentQuestion].id)"
                                            class="h-5 w-5 sm:h-6 sm:w-6 text-blue-600 mt-0.5 focus:ring-blue-500 border-gray-300 flex-shrink-0">
                                        <span class="ml-3 flex-1">
                                            <span class="block text-sm sm:text-base text-gray-700 group-hover:text-gray-900"
                                                :class="{'font-medium text-blue-900': answers[questions[currentQuestion].id] === option.id}"
                                                x-text="option.text"></span>
                                            <template x-if="option.image_path">
                                                <img :src="option.image_path" class="mt-3 max-h-56 w-auto max-w-full rounded-lg border border-gray-200 object-contain bg-white">
                                            </template>
                                        </span>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <template x-if="questions[currentQuestion].type === 'essay'">
                            <div class="space-y-3" x-data="latexPreview('')" x-effect="update(answers[questions[currentQuestion].id] || '')">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-semibold text-gray-700">Jawaban Essay</label>
                                    <button type="button"
                                        @click="$dispatch('open-latex-guide')"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-100 text-xs font-bold hover:bg-blue-100 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3v2.25m4.5-2.25v2.25M4.5 9.75h15M5.625 21h12.75A1.875 1.875 0 0020.25 19.125V8.25a1.875 1.875 0 00-1.875-1.875H5.625A1.875 1.875 0 003.75 8.25v10.875A1.875 1.875 0 005.625 21z" />
                                        </svg>
                                        Panduan Rumus
                                    </button>
                                </div>
                                <textarea
                                    :name="'question_' + questions[currentQuestion].id"
                                    x-model="answers[questions[currentQuestion].id]"
                                    @input.debounce.800ms="saveProgress(questions[currentQuestion].id)"
                                    @blur="saveProgress(questions[currentQuestion].id)"
                                    data-latex-enabled="1"
                                    rows="8"
                                    class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 text-sm sm:text-base"
                                    placeholder="Tulis jawaban Anda di sini..."></textarea>
                                <div class="rounded-lg border border-blue-100 bg-blue-50 p-3">
                                    <p class="text-[11px] font-black uppercase tracking-widest text-blue-700 mb-2">Pratinjau Rumus</p>
                                    <div x-ref="preview" class="min-h-[2.5rem] text-sm text-gray-800"></div>
                                </div>
                                <p class="text-xs text-gray-500">Jawaban disimpan otomatis.</p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer / Navigation -->
                <div class="px-3 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                    <button @click="prevQuestion()" 
                        :disabled="currentQuestion === 0"
                        class="px-3 sm:px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 text-sm sm:text-base font-medium hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed flex items-center transition">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        <span class="hidden sm:inline">Sebelumnya</span>
                        <span class="sm:hidden">Prev</span>
                    </button>

                    <label class="flex items-center cursor-pointer select-none order-first lg:order-none w-full lg:w-auto justify-center">
                        <div class="relative">
                            <input type="checkbox" class="sr-only" x-model="flags[questions[currentQuestion].id]">
                            <div class="w-10 h-6 bg-gray-200 rounded-full shadow-inner transition duration-200" :class="{'bg-yellow-400': flags[questions[currentQuestion].id]}"></div>
                            <div class="dot absolute w-4 h-4 bg-white rounded-full shadow left-1 top-1 transition duration-200 transform" :class="{'translate-x-4': flags[questions[currentQuestion].id]}"></div>
                        </div>
                        <span class="ml-3 text-xs sm:text-sm font-medium text-gray-600">Ragu-ragu</span>
                    </label>

                    <button @click="nextQuestion()" 
                        x-show="currentQuestion < questions.length - 1"
                        class="px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-white text-sm sm:text-base font-medium hover:bg-blue-700 flex items-center transition shadow-sm">
                        <span class="hidden sm:inline">Selanjutnya</span>
                        <span class="sm:hidden">Next</span>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                    
                    <button @click="finishExam()" 
                        x-show="currentQuestion === questions.length - 1"
                        class="px-3 sm:px-4 py-2 bg-green-600 border border-transparent rounded-lg text-white text-sm sm:text-base font-medium hover:bg-green-700 flex items-center transition shadow-sm">
                        Selesai
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Navigation Panel (Right) -->
            <div class="w-full lg:w-80 flex flex-col gap-4 lg:gap-6 mt-4 lg:mt-0">
                <!-- Navigation Grid -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 sm:p-4 flex flex-col">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 sm:mb-4">Navigasi Soal</h3>
                    
                    <div class="grid grid-cols-5 sm:grid-cols-6 lg:grid-cols-5 gap-2 overflow-y-auto max-h-[40vh] lg:max-h-[60vh] p-1">
                        <template x-for="(q, index) in questions" :key="index">
                            <button @click="jumpToQuestion(index)"
                                class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg text-xs sm:text-sm font-bold flex items-center justify-center transition-all duration-200 relative border-2 active:scale-95"
                                :class="{
                                    'border-blue-600 ring-2 ring-blue-200 ring-offset-1': currentQuestion === index,
                                    'bg-green-500 text-white border-green-500 hover:bg-green-600': answers[q.id] && !flags[q.id] && currentQuestion !== index,
                                    'bg-yellow-400 text-white border-yellow-400 hover:bg-yellow-500': flags[q.id] && currentQuestion !== index,
                                    'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200': !answers[q.id] && !flags[q.id] && currentQuestion !== index
                                }">
                                <span x-text="index + 1"></span>
                                <!-- Indicator dots -->
                                <div x-show="currentQuestion === index" class="absolute -top-1 -right-1 w-2 h-2 sm:w-2.5 sm:h-2.5 bg-blue-600 rounded-full border-2 border-white"></div>
                            </button>
                        </template>
                    </div>

                    <div class="mt-auto pt-4 sm:pt-6 border-t border-gray-100">
                        <div class="space-y-1.5 sm:space-y-2 text-[10px] sm:text-xs font-medium text-gray-600">
                            <div class="flex items-center">
                                <div class="w-3 h-3 sm:w-4 sm:h-4 rounded bg-green-500 mr-2 flex-shrink-0"></div> Sudah dijawab
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 sm:w-4 sm:h-4 rounded bg-yellow-400 mr-2 flex-shrink-0"></div> Ragu-ragu
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 sm:w-4 sm:h-4 rounded bg-gray-100 border border-gray-200 mr-2 flex-shrink-0"></div> Belum dijawab
                            </div>
                        </div>
                    </div>
                    
                     <div class="mt-4 sm:mt-6" x-show="currentQuestion === questions.length - 1" x-cloak>
                        <button @click="finishExam()" class="w-full py-2.5 sm:py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm sm:text-base font-bold transition shadow-lg shadow-blue-200 transform hover:-translate-y-0.5 active:scale-95">
                            Kumpulkan Jawaban
                        </button>
                    </div>
                </div>
            </div>
        </div>


    <!-- Start Exam Overlay -->
    <div x-cloak x-show="showStartOverlay" class="fixed inset-0 z-[60] flex items-center justify-center bg-white">
        <div class="text-center max-w-lg px-6">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-blue-100 mb-6">
                <svg class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-4">Ujian Siap Dimulai</h2>
            <p class="text-gray-600 mb-8 text-lg">
                Klik tombol di bawah ini untuk masuk ke mode layar penuh dan memulai waktu ujian.
            </p>
            <button @click="beginExam()" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-lg px-8 py-4 bg-blue-600 text-lg font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500 focus:ring-offset-2 transform transition hover:scale-105">
                MULAI MENGERJAKAN SEKARANG
            </button>
            <p class="mt-4 text-sm text-gray-400">Pastikan browser Anda mengizinkan fitur Fullscreen.</p>
        </div>
    </div>

    <!-- Security Warnings Modal -->
    <div x-cloak x-show="showWarningModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center transform transition-all scale-100">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Peringatan Pelanggaran!</h3>
            <p class="text-gray-600 mb-6">
                <span class="block text-lg font-semibold text-red-600 mb-2" x-text="violationMessage"></span>
                Ini adalah pelanggaran <span class="font-bold text-red-600" x-text="violations"></span> dari <span class="font-bold">{{ $exam->tab_tolerance ?? 3 }}</span>.
                <br><br>
                Jika mencapai batas, ujian akan diblokir sementara sampai guru memutuskan lanjut atau akhiri.
            </p>
            <button @click="resumeExam()" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                Saya Mengerti, Kembali ke Ujian
            </button>
        </div>
    </div>

    <!-- Blocked Overlay -->
    <div x-cloak x-show="isBlocked" class="fixed inset-0 z-[70] flex items-center justify-center bg-white/95 backdrop-blur-sm">
        <div class="max-w-lg w-full mx-6 rounded-2xl border border-red-200 bg-white p-8 text-center shadow-2xl">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                <svg class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01m-8-4a8 8 0 1116 0 8 8 0 01-16 0z" />
                </svg>
            </div>
            <h3 class="text-2xl font-black text-red-700 mb-2">Ujian Diblokir Sementara</h3>
            <p class="text-sm text-gray-600 mb-4" x-text="blockMessage"></p>
            <p class="text-xs text-gray-400 uppercase tracking-wider font-bold">Menunggu keputusan guru/pengawas</p>
        </div>
    </div>

    <!-- Finish Confirmation Modal -->
     <div x-cloak x-show="showFinishModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 transform transition-all scale-100">
             <div class="mb-4">
                <h3 class="text-xl font-bold text-gray-900">Konfirmasi Selesai Ujian</h3>
                <p class="text-sm text-gray-500 mt-1">Apakah Anda yakin ingin menyelesaikan ujian ini?</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-green-600" x-text="Object.keys(answers).length"></div>
                        <div class="text-xs text-gray-500 uppercase font-semibold">Terjawab</div>
                    </div>
                     <div>
                        <div class="text-2xl font-bold text-gray-400" x-text="questions.length - Object.keys(answers).length"></div>
                        <div class="text-xs text-gray-500 uppercase font-semibold">Belum Dijawab</div>
                    </div>
                     <div class="col-span-2 border-t border-gray-200 pt-3 mt-1" x-show="Object.keys(flags).filter(k => flags[k]).length > 0">
                        <div class="text-xl font-bold text-yellow-500" x-text="Object.keys(flags).filter(k => flags[k]).length"></div>
                        <div class="text-xs text-gray-500 uppercase font-semibold">Tanda Ragu-ragu</div>
                        <p class="text-xs text-red-500 mt-1">Harap cek kembali jawaban ragu-ragu.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button @click="showFinishModal = false" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 focus:outline-none">
                    Batal
                </button>
                 <button @click="submitExam()" class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-white font-medium hover:bg-blue-700 focus:outline-none shadow-sm">
                    Ya, Selesaikan Sekarang
                </button>
            </div>
        </div>
    </div>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            @php
                $hours = floor($remainingSeconds / 3600);
                $minutes = floor(($remainingSeconds % 3600) / 60);
                $seconds = $remainingSeconds % 60;
                $initialFormattedTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            @endphp
            
            Alpine.store('exam', {
                timeLeft: {{ $remainingSeconds }}, // Dynamic - accounts for late starts
                formattedTime: '{{ $initialFormattedTime }}',
                isActive: false,
                
                startTimer() {
                    this.isActive = true;
                    // Timer logic moved here or controlled by isActive
                },

                updateTime() {
                    if (this.timeLeft > 0 && this.isActive) {
                        this.timeLeft--;
                        const h = Math.floor(this.timeLeft / 3600).toString().padStart(2, '0');
                        const m = Math.floor((this.timeLeft % 3600) / 60).toString().padStart(2, '0');
                        const s = (this.timeLeft % 60).toString().padStart(2, '0');
                        this.formattedTime = `${h}:${m}:${s}`;
                    }
                }
            });

            Alpine.data('examData', () => ({
                currentQuestion: 0,
                attemptId: {{ (int) $attempt->id }},
                answers: @json((object)$existingAnswers),
                flags: {},
                violations: {{ (int) ($attempt->tab_switches ?? 0) }},
                violationMessage: '',
                isBlocked: {{ ($attempt->status instanceof \App\Enums\ExamAttemptStatus ? $attempt->status === \App\Enums\ExamAttemptStatus::Blocked : (string)$attempt->status === 'blocked') ? 'true' : 'false' }},
                blockMessage: 'Ujian Anda diblokir sementara karena pelanggaran. Menunggu keputusan guru/pengawas.',
                needsFullscreen: false,
                screenshotLockUntil: 0,
                showWarningModal: false,
                showFinishModal: false,
                showStartOverlay: true,
                questions: @json($questions),
                pendingSaves: {},
                isOffline: !navigator.onLine,
                saveErrorMessage: '',
                lastHeartbeatFailed: false,
                isRetryingSaves: false,
                isSubmitting: false,
                statusPollTick: 0,

                async postJson(url, payload) {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    });

                    let data = {};
                    try {
                        data = await response.json();
                    } catch (e) {
                        // Ignore parse failure, handled by status check below.
                    }

                    if (!response.ok || data.success === false) {
                        const message = data.message || `Request failed (${response.status})`;
                        throw new Error(message);
                    }

                    return data;
                },

                markSaveFailed(questionId, answer, message) {
                    this.pendingSaves[questionId] = answer;
                    this.saveErrorMessage = message || 'Gagal menyimpan jawaban. Akan dicoba lagi otomatis.';
                },

                async flushPendingSaves() {
                    if (this.isRetryingSaves || this.showFinishModal) return;
                    if (!navigator.onLine) {
                        this.isOffline = true;
                        return;
                    }

                    const entries = Object.entries(this.pendingSaves);
                    if (entries.length === 0) {
                        this.saveErrorMessage = '';
                        return;
                    }

                    this.isRetryingSaves = true;

                    try {
                        for (const [questionId, answer] of entries) {
                            await this.postJson('{{ route('student.exam.save-answer', $exam->id) }}', {
                                question_id: Number(questionId),
                                answer: answer
                            });
                            delete this.pendingSaves[questionId];
                        }

                        this.saveErrorMessage = '';
                    } catch (error) {
                        this.saveErrorMessage = 'Gagal menyimpan jawaban. Akan dicoba lagi otomatis.';
                    } finally {
                        this.isRetryingSaves = false;
                    }
                },

                async sendHeartbeat() {
                    if (!Alpine.store('exam').isActive || !navigator.onLine || this.isBlocked) {
                        return;
                    }

                    try {
                        await this.postJson('{{ route('student.exam.heartbeat', $exam->id) }}', {});
                        this.lastHeartbeatFailed = false;
                    } catch (e) {
                        this.lastHeartbeatFailed = true;
                    }
                },

                async handleOnline() {
                    this.isOffline = false;
                    await this.flushPendingSaves();
                    await this.sendHeartbeat();
                },

                handleOffline() {
                    this.isOffline = true;
                    this.saveErrorMessage = 'Koneksi internet terputus. Jawaban disimpan lokal sementara.';
                },

                handleViolation(message, forcedType = null) {
                    if (this.showFinishModal || this.isBlocked) return; 

                    const type = forcedType ?? (document.hidden ? 'tab_switch' : 'fullscreen_exit');
                    this.violationMessage = message || 'Terjadi pelanggaran aturan ujian.';
                    
                    const maxViolations = {{ $exam->tab_tolerance ?? 3 }};
                    
                    // Persist violation server-side so refresh cannot reset the counter.
                    this.postJson('{{ route('student.exam.log-violation', $exam->id) }}', {
                        type: type,
                        message: this.violationMessage,
                        count: 1
                    }).then((data) => {
                        if (typeof data.tab_switches === 'number') {
                            this.violations = data.tab_switches;
                        } else {
                            this.violations++;
                        }

                        if (data.blocked || this.violations >= maxViolations) {
                            this.isBlocked = true;
                            this.blockMessage = data.message || 'Ujian Anda diblokir sementara. Menunggu keputusan guru/pengawas.';
                            this.showWarningModal = false;
                            Alpine.store('exam').isActive = false;
                            if (document.fullscreenElement) {
                                document.exitFullscreen().catch(() => {});
                            }
                            return;
                        }

                        this.showWarningModal = true;
                        if (document.fullscreenElement) {
                            document.exitFullscreen().catch(() => {});
                        }
                    }).catch(() => {
                        // If request fails (offline), keep local count. Server-side statusCheck will enforce once connected.
                        this.violations++;
                        this.showWarningModal = true;
                        if (document.fullscreenElement) {
                            document.exitFullscreen().catch(() => {});
                        }
                    });
                },

                isScreenshotAttempt(event) {
                    const key = String(event.key || '').toLowerCase();

                    if (key === 'printscreen') {
                        return true;
                    }

                    // Legacy keyboard event path for PrintScreen in some browsers.
                    if (event.keyCode === 44) {
                        return true;
                    }

                    // Common desktop screenshot shortcuts (best effort only).
                    if (event.metaKey && event.shiftKey && ['3', '4', '5'].includes(key)) {
                        return true;
                    }

                    if (event.ctrlKey && event.shiftKey && ['s'].includes(key)) {
                        return true;
                    }

                    return false;
                },

                handleScreenshotAttempt() {
                    const now = Date.now();
                    if (now < this.screenshotLockUntil) {
                        return;
                    }

                    this.screenshotLockUntil = now + 1500;
                    this.handleViolation(
                        'Percobaan screenshot terdeteksi. Screenshot tidak diizinkan selama ujian.',
                        'fullscreen_exit'
                    );
                },

                async checkStatus() {
                    if (this.isSubmitting || this.showFinishModal) return;

                    try {
                        const res = await fetch('{{ route('student.exam.status_check', $exam->id) }}', {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await res.json();
                        if (data.blocked) {
                            this.isBlocked = true;
                            this.blockMessage = data.message || this.blockMessage;
                            Alpine.store('exam').isActive = false;
                            return;
                        }
                        if (this.isBlocked && !data.blocked) {
                            this.isBlocked = false;
                            this.blockMessage = 'Ujian Anda diblokir sementara karena pelanggaran. Menunggu keputusan guru/pengawas.';
                            Alpine.store('exam').isActive = true;
                        }
                        if (!data.force_stop) return;

                        // Silent redirect. Disable beforeunload so Chrome doesn't show confirmation.
                        this.isSubmitting = true;
                        window.onbeforeunload = null;
                        await this.flushPendingSaves();
                        window.location.href = data.redirect;
                    } catch (e) {
                        // ignore
                    }
                },

                tryEnterFullscreen() {
                    if (this.isBlocked) {
                        this.needsFullscreen = false;
                        return Promise.resolve();
                    }
                    // Browsers often require a user gesture for fullscreen.
                    // We attempt anyway, and if blocked we show a banner and retry on next click.
                    return document.documentElement.requestFullscreen()
                        .then(() => { this.needsFullscreen = false; })
                        .catch(() => { this.needsFullscreen = true; });
                },

                initExam() {
                    // Auto-resume after refresh (prevents going back to "MULAI..." overlay).
                    // Uses sessionStorage so it's per-tab session and doesn't create a permanent bypass.
                    const startedKey = `exam_started_attempt_${this.attemptId}`;
                    if (sessionStorage.getItem(startedKey) === '1' && !this.isBlocked) {
                        this.showStartOverlay = false;
                        Alpine.store('exam').startTimer();
                        this.sendHeartbeat();
                        this.tryEnterFullscreen();

                        // If fullscreen is blocked, retry once on the next user click (counts as a gesture).
                        const retryOnClick = () => {
                            if (!this.needsFullscreen) {
                                window.removeEventListener('click', retryOnClick, true);
                                return;
                            }
                            this.tryEnterFullscreen().finally(() => {
                                window.removeEventListener('click', retryOnClick, true);
                            });
                        };
                        window.addEventListener('click', retryOnClick, true);
                    }

                    if (this.isBlocked) {
                        this.showStartOverlay = false;
                        Alpine.store('exam').isActive = false;
                    }

                    // Initial math render (KaTeX might load slightly after Alpine; retry briefly).
                    const tryRender = (attempt = 0) => {
                        if (attempt > 40) return;
                        if (typeof window.renderMathInElement !== 'function') {
                            setTimeout(() => tryRender(attempt + 1), 50);
                            return;
                        }
                        window.renderKatexIn(this.$refs.mathRoot);
                    };
                    this.$nextTick(() => tryRender());

                    window.addEventListener('online', () => this.handleOnline());
                    window.addEventListener('offline', () => this.handleOffline());

                    // Timer
                    setInterval(() => {
                        Alpine.store('exam').updateTime();
                        this.statusPollTick++;
                        
                        // Status check every 5 seconds even when timer is paused (e.g. blocked state).
                        if (this.statusPollTick % 5 === 0) {
                            this.checkStatus();
                        }

                        if (Alpine.store('exam').timeLeft <= 0 && Alpine.store('exam').isActive && !this.isBlocked) {
                            this.submitExam();
                        }
                    }, 1000);

                    // Retry failed saves periodically.
                    setInterval(() => {
                        this.flushPendingSaves();
                    }, 5000);

                    // Heartbeat to keep session/attempt presence fresh.
                    setInterval(() => {
                        this.sendHeartbeat();
                    }, 15000);

                    // Visibility Change (Anti-Cheat) - Only if enabled
                    @if($exam->enable_tab_tolerance)
                    document.addEventListener('visibilitychange', () => {
                         if (document.hidden && Alpine.store('exam').isActive && !this.isBlocked) {
                            this.handleViolation('Anda terdeteksi meninggalkan halaman ujian (pindah tab/minimize).');
                        }
                    });

                    // Snipping Tool and some OS overlays do not always emit key events.
                    // Treat focus loss during active exam as a violation as well.
                    window.addEventListener('blur', () => {
                        if (!Alpine.store('exam').isActive || this.showFinishModal || this.showWarningModal || this.isBlocked) {
                            return;
                        }

                        setTimeout(() => {
                            if (!document.hasFocus() && Alpine.store('exam').isActive && !this.showFinishModal && !this.showWarningModal && !this.isBlocked) {
                                this.handleViolation(
                                    'Fokus jendela ujian terdeteksi keluar. Ini dianggap pelanggaran.',
                                    'fullscreen_exit'
                                );
                            }
                        }, 120);
                    });
                    @endif

                    // Fullscreen Exit Detection - Only if enabled
                    @if($exam->enable_tab_tolerance)
                    const onFullscreenChange = () => {
                        if (!document.fullscreenElement && Alpine.store('exam').isActive && !this.showFinishModal && !this.showWarningModal && !this.isBlocked) {
                            this.handleViolation('Anda keluar dari mode layar penuh (fullscreen).');
                        }
                    };

                    document.addEventListener('fullscreenchange', onFullscreenChange);
                    document.addEventListener('webkitfullscreenchange', onFullscreenChange);
                    document.addEventListener('mozfullscreenchange', onFullscreenChange);
                    document.addEventListener('msfullscreenchange', onFullscreenChange);
                    @endif

                    // Keep a lightweight indicator for fullscreen state while exam is active.
                    document.addEventListener('fullscreenchange', () => {
                        if (Alpine.store('exam').isActive) {
                            this.needsFullscreen = !document.fullscreenElement;
                        }
                    });

                    // Disable Right Click
                    document.addEventListener('contextmenu', (e) => {
                        e.preventDefault();
                    });

                    // Disable Keyboard Shortcuts
                    document.addEventListener('keydown', (e) => {
                        @if($exam->enable_tab_tolerance)
                        if (this.isScreenshotAttempt(e) && Alpine.store('exam').isActive) {
                            e.preventDefault();
                            this.handleScreenshotAttempt();
                            return;
                        }
                        @endif

                        // F12
                        if (e.key === 'F12') {
                            e.preventDefault();
                        }
                        
                        // Ctrl+C, Ctrl+V, Ctrl+U, Ctrl+Shift+I
                        if (e.ctrlKey && (e.key === 'c' || e.key === 'C' || e.key === 'v' || e.key === 'V' || e.key === 'u' || e.key === 'U')) {
                            e.preventDefault();
                        }
                        
                        if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j' || e.key === 'C' || e.key === 'c')) {
                            e.preventDefault();
                        }
                    });
                    
                    // Prevent closing window
                    window.onbeforeunload = function() {
                        return "Apakah Anda yakin ingin meninggalkan halaman?";
                    };
                },

                beginExam() {
                    if (this.isBlocked) {
                        return;
                    }
                    this.showStartOverlay = false;
                    try {
                        sessionStorage.setItem(`exam_started_attempt_${this.attemptId}`, '1');
                    } catch (e) {
                        // Ignore storage errors.
                    }
                    Alpine.store('exam').startTimer();
                    this.sendHeartbeat();
                    this.tryEnterFullscreen();
                },

                prevQuestion() {
                    if (this.currentQuestion > 0) this.currentQuestion--;
                    this.$nextTick(() => window.renderKatexIn(this.$refs.mathRoot));
                },

                nextQuestion() {
                    if (this.currentQuestion < this.questions.length - 1) this.currentQuestion++;
                    this.$nextTick(() => window.renderKatexIn(this.$refs.mathRoot));
                },

                jumpToQuestion(index) {
                    this.currentQuestion = index;
                    this.$nextTick(() => window.renderKatexIn(this.$refs.mathRoot));
                },
                
                resumeExam() {
                    this.showWarningModal = false;
                    if (this.isBlocked) {
                        return;
                    }
                    document.documentElement.requestFullscreen().catch((e) => {});
                },

                finishExam() {
                    if (this.isBlocked) {
                        return;
                    }
                    if (this.currentQuestion !== this.questions.length - 1) {
                        return;
                    }
                    this.showFinishModal = true;
                },

                async submitExam() {
                     if (this.isBlocked) {
                         return;
                     }
                     this.isSubmitting = true;
                     await this.flushPendingSaves();

                     // Remove unload listener
                     window.onbeforeunload = null;
                     
                     // Exit fullscreen first
                     if (document.fullscreenElement) {
                        document.exitFullscreen().catch(() => {});
                     }

                     this.postJson('{{ route('student.exam.submit', $exam->id) }}', { answers: this.answers })
                     .then(data => {
                         if(data.success) {
                             try {
                                 sessionStorage.removeItem(`exam_started_attempt_${this.attemptId}`);
                             } catch (e) {
                                 // Ignore storage errors.
                             }
                             window.location.href = data.redirect;
                         } else {
                             this.isSubmitting = false;
                             alert('Terjadi kesalahan saat menyimpan jawaban.');
                         }
                     })
                     .catch(error => {
                         this.isSubmitting = false;
                         console.error('Error:', error);
                         alert('Gagal mengirim jawaban. Periksa koneksi internet Anda.');
                     });
                },

                saveProgress(questionId) {
                    if (this.isBlocked) return;
                    const answer = this.answers[questionId];
                    if (answer === undefined || answer === null) return;

                    this.pendingSaves[questionId] = answer;

                    this.flushPendingSaves().catch(() => {
                        this.markSaveFailed(questionId, answer, 'Gagal menyimpan jawaban. Akan dicoba lagi otomatis.');
                    });
                }
            }));
            
            // Watch logic moved outside or use x-init inside the component
        });
    </script>

    @push('modals')
        <x-latex-guide-modal />
    @endpush
</x-exam-layout>
