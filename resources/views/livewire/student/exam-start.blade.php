<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="text-center space-y-2">
        <h1 class="text-2xl font-bold text-gray-900">{{ $exam->name }}</h1>
        <p class="text-gray-500">{{ $exam->subject->name }} • {{ $exam->teacher->user->name }}</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <!-- Main Info Card -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Informasi Ujian</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <div class="text-xs text-gray-500 mb-1">Tanggal</div>
                        <div class="font-semibold text-gray-900">{{ $exam->date->translatedFormat('l, d F Y') }}</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <div class="text-xs text-gray-500 mb-1">Waktu</div>
                        <div class="font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($exam->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($exam->end_time)->format('H:i') }}
                        </div>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-xl">
                        <div class="text-xs text-blue-600 mb-1">Durasi</div>
                        <div class="font-semibold text-blue-700">{{ $exam->duration_minutes }} Menit</div>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-xl">
                        <div class="text-xs text-purple-600 mb-1">Jumlah Soal</div>
                        <div class="font-semibold text-purple-700">{{ $exam->questions->count() }} Soal</div>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-100">
                    <h4 class="font-medium text-gray-900 mb-3">Aturan Pengerjaan:</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Waktu akan berjalan otomatis saat tombol "Mulai Ujian" ditekan.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Jawaban akan tersimpan secara otomatis setiap kali Anda memilih opsi.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Dilarang berpindah tab atau keluar dari mode fullscreen (jika diaktifkan).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Jika waktu habis, jawaban yang sudah terisi akan otomatis disubmit.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Sidebar / Action Card -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm sticky top-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-3 text-blue-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">Passing Grade</p>
                    <div class="text-2xl font-bold text-gray-900">{{ $exam->passing_grade }}</div>
                </div>

                @if($this->canStart())
                    <button wire:click="startExam" class="w-full py-3 px-4 bg-primary text-white font-medium rounded-xl hover:bg-primary-600 transition-all shadow-lg shadow-primary/25 flex items-center justify-center gap-2 group">
                        <span>Mulai Ujian</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                    <p class="text-center text-xs text-gray-500 mt-3">
                        Dengan menekan tombol ini, durasi ujian akan dimulai.
                    </p>
                @else
                    <button disabled class="w-full py-3 px-4 bg-gray-100 text-gray-400 font-medium rounded-xl cursor-not-allowed">
                        Belum Bisa Dimulai
                    </button>
                    <p class="text-center text-xs text-gray-400 mt-3">
                        Ujian hanya dapat dimulai pada waktu yang telah ditentukan.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
