<x-exam-layout title="Ujian Matematika">
    <x-slot name="header_actions">
        <!-- Header Actions: User Info & Timer -->
        <div class="flex items-center space-x-4" x-data>
            <div class="hidden sm:flex flex-col items-end mr-4">
                <span class="text-sm font-semibold text-gray-700">Ahmad Siswa</span>
                <span class="text-xs text-gray-500">NIS: 123456</span>
            </div>
            
            <!-- Timer Badge -->
            <div class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg border border-blue-100 font-mono font-bold text-lg flex items-center shadow-sm">
                <svg class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-text="$store.exam.formattedTime" :class="{'text-red-600 animate-pulse': $store.exam.timeLeft < 300}"></span>
            </div>
        </div>
    </x-slot>

    <!-- Main Exam UI -->
    <div x-data="examData()" x-init="initExam()" class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 h-full">
        
        <div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-8rem)]">
            
            <!-- Question Area (Left) -->
            <div class="flex-1 flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden h-full">
                <!-- Question Header -->
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h2 class="text-lg font-bold text-gray-800">
                        Soal No. <span x-text="currentQuestion + 1"></span>
                    </h2>
                    <div class="flex items-center space-x-2">
                        <span x-show="questions[currentQuestion].type === 'multiple_choice'" class="text-xs font-medium bg-gray-200 text-gray-600 px-2 py-1 rounded">Pilihan Ganda</span>
                    </div>
                </div>

                <!-- Question Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="prose max-w-none text-gray-800 text-lg mb-8">
                        <div x-html="questions[currentQuestion].text"></div>
                    </div>

                    <!-- Options -->
                    <div class="space-y-4">
                        <template x-for="(option, index) in questions[currentQuestion].options" :key="index">
                            <label class="flex items-start p-4 rounded-lg border-2 cursor-pointer transition-all duration-200 group hover:bg-gray-50"
                                :class="{
                                    'border-blue-600 bg-blue-50': answers[questions[currentQuestion].id] === option.id,
                                    'border-gray-200': answers[questions[currentQuestion].id] !== option.id
                                }">
                                <input type="radio" 
                                    :name="'question_' + questions[currentQuestion].id" 
                                    :value="option.id"
                                    x-model="answers[questions[currentQuestion].id]"
                                    class="h-5 w-5 text-blue-600 mt-0.5 focus:ring-blue-500 border-gray-300">
                                <span class="ml-3 text-gray-700 group-hover:text-gray-900" 
                                    :class="{'font-medium text-blue-900': answers[questions[currentQuestion].id] === option.id}" 
                                    x-text="option.text"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Footer / Navigation -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <button @click="prevQuestion()" 
                        :disabled="currentQuestion === 0"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed flex items-center transition">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        Sebelumnya
                    </button>

                    <label class="flex items-center cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" class="sr-only" x-model="flags[questions[currentQuestion].id]">
                            <div class="w-10 h-6 bg-gray-200 rounded-full shadow-inner transition duration-200" :class="{'bg-yellow-400': flags[questions[currentQuestion].id]}"></div>
                            <div class="dot absolute w-4 h-4 bg-white rounded-full shadow left-1 top-1 transition duration-200 transform" :class="{'translate-x-4': flags[questions[currentQuestion].id]}"></div>
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-600">Ragu-ragu</span>
                    </label>

                    <button @click="nextQuestion()" 
                        x-show="currentQuestion < questions.length - 1"
                        class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-white font-medium hover:bg-blue-700 flex items-center transition shadow-sm">
                        Selanjutnya
                        <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                    
                    <button @click="finishExam()" 
                        x-show="currentQuestion === questions.length - 1"
                        class="px-4 py-2 bg-green-600 border border-transparent rounded-lg text-white font-medium hover:bg-green-700 flex items-center transition shadow-sm">
                        Selesai
                        <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Navigation Panel (Right) -->
            <div class="w-full lg:w-80 flex flex-col gap-6">
                <!-- Navigation Grid -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col h-full">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Navigasi Soal</h3>
                    
                    <div class="grid grid-cols-5 gap-2 overflow-y-auto max-h-[60vh] p-1">
                        <template x-for="(q, index) in questions" :key="index">
                            <button @click="jumpToQuestion(index)"
                                class="w-10 h-10 rounded-lg text-sm font-bold flex items-center justify-center transition-all duration-200 relative border-2"
                                :class="{
                                    'border-blue-600 ring-2 ring-blue-200 ring-offset-1': currentQuestion === index,
                                    'bg-green-500 text-white border-green-500 hover:bg-green-600': answers[q.id] && !flags[q.id] && currentQuestion !== index,
                                    'bg-yellow-400 text-white border-yellow-400 hover:bg-yellow-500': flags[q.id] && currentQuestion !== index,
                                    'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200': !answers[q.id] && !flags[q.id] && currentQuestion !== index
                                }">
                                <span x-text="index + 1"></span>
                                <!-- Indicator dots -->
                                <div x-show="currentQuestion === index" class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-blue-600 rounded-full border-2 border-white"></div>
                            </button>
                        </template>
                    </div>

                    <div class="mt-auto pt-6 border-t border-gray-100">
                        <div class="space-y-2 text-xs font-medium text-gray-600">
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded bg-green-500 mr-2"></div> Sudah dijawab
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded bg-yellow-400 mr-2"></div> Ragu-ragu
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded bg-gray-100 border border-gray-200 mr-2"></div> Belum dijawab
                            </div>
                        </div>
                    </div>
                    
                     <div class="mt-6">
                        <button @click="finishExam()" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition shadow-lg shadow-blue-200 transform hover:-translate-y-0.5">
                            Kumpulkan Jawaban
                        </button>
                    </div>
                </div>
            </div>
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
                Anda terdeteksi meninggalkan halaman ujian. Ini adalah pelanggaran <span class="font-bold text-red-600" x-text="violations"></span> dari <span class="font-bold">3</span>.
                <br><br>
                Jika Anda melanggar lebih dari 3 kali, ujian akan otomatis dihentikan.
            </p>
            <button @click="resumeExam()" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                Saya Mengerti, Kembali ke Ujian
            </button>
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

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('exam', {
                timeLeft: 3600, // 60 minutes in seconds
                formattedTime: '01:00:00',
                
                updateTime() {
                    if (this.timeLeft > 0) {
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
                answers: {},
                flags: {},
                violations: 0,
                showWarningModal: false,
                showFinishModal: false,
                questions: [
                    {
                        id: 1,
                        type: 'multiple_choice',
                        text: '<p>Jika seorang pedagang membeli barang seharga Rp 100.000 dan menjualnya dengan harga Rp 120.000, berapakah persentase keuntungannya?</p>',
                        options: [
                            { id: 'A', text: '10%' },
                            { id: 'B', text: '15%' },
                            { id: 'C', text: '20%' },
                            { id: 'D', text: '25%' },
                            { id: 'E', text: '30%' }
                        ]
                    },
                    {
                        id: 2,
                        type: 'multiple_choice',
                        text: '<p>Himpunan penyelesaian dari persamaan 2x + 5 = 11 adalah...</p>',
                        options: [
                            { id: 'A', text: '{2}' },
                            { id: 'B', text: '{3}' },
                            { id: 'C', text: '{4}' },
                            { id: 'D', text: '{5}' },
                            { id: 'E', text: '{6}' }
                        ]
                    },
                    {
                        id: 3,
                        type: 'multiple_choice',
                        text: '<p>Salah satu akar persamaan kuadrat x² - 5x + 6 = 0 adalah...</p>',
                        options: [
                            { id: 'A', text: '1' },
                            { id: 'B', text: '2' },
                            { id: 'C', text: '4' },
                            { id: 'D', text: '5' },
                            { id: 'E', text: '6' }
                        ]
                    },
                     {
                        id: 4,
                        type: 'multiple_choice',
                        text: '<p>Nilai dari 2⁵ adalah...</p>',
                        options: [
                            { id: 'A', text: '16' },
                            { id: 'B', text: '25' },
                            { id: 'C', text: '32' },
                            { id: 'D', text: '64' },
                            { id: 'E', text: '128' }
                        ]
                    }
                ],

                initExam() {
                    // Fullscreen request
                    // document.documentElement.requestFullscreen().catch((e) => console.log(e));

                    // Timer
                    setInterval(() => {
                        Alpine.store('exam').updateTime();
                        if (Alpine.store('exam').timeLeft <= 0) {
                            this.submitExam();
                        }
                    }, 1000);

                    // Visibility Change (Anti-Cheat)
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden) {
                            this.violations++;
                            if (this.violations >= 3) {
                                alert('Anda telah melanggar batas toleransi meninggalkan ujian. Ujian akan dikirim otomatis.');
                                this.submitExam();
                            } else {
                                this.showWarningModal = true;
                            }
                        }
                    });
                    
                    // Prevent closing window
                    window.onbeforeunload = function() {
                        return "Apakah Anda yakin ingin meninggalkan halaman?";
                    };
                },

                prevQuestion() {
                    if (this.currentQuestion > 0) this.currentQuestion--;
                },

                nextQuestion() {
                    if (this.currentQuestion < this.questions.length - 1) this.currentQuestion++;
                },

                jumpToQuestion(index) {
                    this.currentQuestion = index;
                },
                
                resumeExam() {
                    this.showWarningModal = false;
                    // Try fullscreen again
                    // document.documentElement.requestFullscreen().catch((e) => {});
                },

                finishExam() {
                    this.showFinishModal = true;
                },

                submitExam() {
                     // In real app, submit form here
                     alert('Ujian Selesai! Jawaban tersimpan.');
                     window.location.href = "{{ route('student.dashboard') }}";
                }
            }));
        });
    </script>
</x-exam-layout>
