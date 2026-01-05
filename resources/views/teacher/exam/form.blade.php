@section('title', $examId ? 'Edit Ujian' : 'Jadwalkan Ujian Baru')

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('teacher.exams.index') }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h2 class="font-bold text-2xl text-text-main">
            {{ $examId ? 'Edit Ujian' : 'Buat Ujian Baru' }}
        </h2>
    </div>

    <!-- Steps Indicator -->
    <div class="flex items-center justify-center mb-8">
        <div class="flex items-center">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors {{ $step >= 1 ? 'bg-primary text-white' : 'bg-gray-200 text-text-muted' }}">
                    1
                </div>
                <span class="text-sm font-medium mt-1 {{ $step >= 1 ? 'text-primary' : 'text-text-muted' }}">Detail Ujian</span>
            </div>
            
            <div class="w-24 h-1 bg-gray-200 mx-2">
                <div class="h-full bg-primary transition-all duration-300" style="width: {{ $step >= 2 ? '100%' : '0%' }}"></div>
            </div>

            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors {{ $step >= 2 ? 'bg-primary text-white' : 'bg-gray-200 text-text-muted' }}">
                    2
                </div>
                <span class="text-sm font-medium mt-1 {{ $step >= 2 ? 'text-primary' : 'text-text-muted' }}">Pilih Soal</span>
            </div>
        </div>
    </div>

    <div class="bg-bg-surface rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <form wire:submit.prevent="save" class="p-6 space-y-6">
            
            <!-- Step 1: Basic Info & Settings -->
            @if($step === 1)
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-text-main mb-1">Nama Ujian</label>
                        <input type="text" wire:model="name" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main" placeholder="Contoh: Ujian Harian Matematika Bab 1">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-text-main mb-1">Mata Pelajaran</label>
                        <select wire:model="subject" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main">
                            <option value="">Pilih Mata Pelajaran</option>
                            <option value="Matematika">Matematika</option>
                            <option value="Biologi">Biologi</option>
                            <option value="Sejarah">Sejarah</option>
                            <option value="Fisika">Fisika</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex justify-between items-end mb-3">
                            <label class="block text-sm font-medium text-text-main">Kelas Peserta</label>
                            <div class="flex gap-2">
                                <button type="button" wire:click="toggleLevel('X')" class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-gray-600 transition-colors">Pilih Semua Kelas X</button>
                                <button type="button" wire:click="toggleLevel('XI')" class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-gray-600 transition-colors">Pilih Semua Kelas XI</button>
                                <button type="button" wire:click="toggleLevel('XII')" class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-gray-600 transition-colors">Pilih Semua Kelas XII</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            @foreach($availableClasses as $class)
                            <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors {{ in_array($class, $classes) ? 'ring-2 ring-primary border-transparent' : '' }}">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" wire:model="classes" value="{{ $class }}" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                                </div>
                                <span class="text-sm text-gray-700 font-medium select-none">{{ $class }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('classes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-text-main mb-1">Tanggal Ujian</label>
                         <input type="date" wire:model="date" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main">
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-text-main mb-1">Durasi (Menit)</label>
                         <input type="number" wire:model="duration" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main" placeholder="90">
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-text-main mb-1">Waktu Mulai</label>
                         <input type="time" wire:model="start_time" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main">
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-text-main mb-1">Waktu Selesai</label>
                         <input type="time" wire:model="end_time" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main">
                    </div>
                    
                    <div>
                         <label class="block text-sm font-medium text-text-main mb-1">Passing Grade</label>
                         <input type="number" wire:model="passing_grade" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main" placeholder="70">
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-text-main mb-1">Bobot Poin Default</label>
                         <input type="number" wire:model="default_score" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main" placeholder="5">
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-medium text-text-main mb-1">Kode Ujian (Token)</label>
                        <div class="flex gap-2">
                            <input type="text" wire:model="token" class="flex-1 border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main font-mono text-lg font-bold tracking-widest uppercase" placeholder="TOKEN">
                            <button type="button" wire:click="regenerateToken" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Generate
                            </button>
                        </div>
                        <p class="text-xs text-text-muted mt-1">Kode ini akan digunakan siswa untuk masuk ke ujian.</p>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h3 class="font-medium text-text-main mb-4">Pengaturan Tambahan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" wire:model="shuffle_questions" class="w-5 h-5 text-primary rounded focus:ring-primary border-gray-300">
                            <span class="text-sm text-text-main">Acak Urutan Soal</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" wire:model="shuffle_answers" class="w-5 h-5 text-primary rounded focus:ring-primary border-gray-300">
                            <span class="text-sm text-text-main">Acak Pilihan Jawaban</span>
                        </label>


                        
                         <div class="p-3 border border-gray-200 rounded-lg">
                             <label class="block text-sm font-medium text-text-main mb-1">Toleransi Pindah Tab</label>
                             <div class="flex items-center gap-2">
                                 <input type="range" wire:model="tab_tolerance" min="0" max="10" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                                 <span class="text-sm font-bold text-primary w-8 text-center">{{ $tab_tolerance }}x</span>
                             </div>
                         </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Step 2: Question Selection -->
            @if($step === 2)
            <div class="space-y-6">
                <div class="flex justify-between items-center bg-blue-50 p-4 rounded-lg text-primary">
                    <span class="font-medium">Total Soal Terpilih: <span class="font-bold text-xl ml-1">0</span></span>
                    <span class="text-sm">Total Poin: 0</span>
                </div>

                <!-- Search & Filters -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <input type="text" class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-primary focus:border-primary" placeholder="Cari soal dari bank soal...">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <div class="w-full sm:w-48">
                        <select wire:model="filterSubject" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main">
                            <option value="">Semua Mapel</option>
                            <option value="Matematika">Matematika</option>
                            <option value="Biologi">Biologi</option>
                            <option value="Fisika">Fisika</option>
                            <option value="Kimia">Kimia</option>
                        </select>
                    </div>
                </div>

                <!-- Dummy List of Questions to Add -->
                <div class="border border-gray-200 rounded-lg overflow-hidden divide-y divide-gray-100 h-96 overflow-y-auto">
                    <!-- Item 1 -->
                    @for($i = 1; $i <= 5; $i++)
                    <div class="p-4 flex items-start gap-3 hover:bg-gray-50 transition-colors cursor-pointer group">
                        <input type="checkbox" class="mt-1 w-4 h-4 text-primary rounded focus:ring-primary border-gray-300">
                        <div class="flex-1">
                            <p class="text-sm text-text-main font-medium">Contoh soal nomor {{ $i }} dari bank soal...</p>
                            <div class="flex gap-2 mt-1">
                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded">Pilihan Ganda</span>
                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded">10 Poin</span>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>

                <div class="flex justify-center">
                    <button type="button" class="text-primary text-sm font-medium hover:underline">
                        + Buat Soal Baru (Langsung)
                    </button>
                </div>
            </div>
            @endif

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                @if($step > 1)
                    <button type="button" wire:click="prevStep" class="px-4 py-2 border border-gray-200 rounded-lg text-text-main hover:bg-gray-50 font-medium transition-colors">
                        Kembali
                    </button>
                @else
                    <a href="{{ route('teacher.exams.index') }}" class="px-4 py-2 hover:bg-gray-100 rounded-lg text-text-muted font-medium transition-colors">Batal</a>
                @endif
                
                @if($step < 2)
                    <button type="button" wire:click="nextStep" class="px-6 py-2 bg-primary hover:bg-blue-700 text-white rounded-lg font-medium transition-colors shadow-sm ml-auto">
                        Lanjut: Pilih Soal &rarr;
                    </button>
                @else
                    <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors shadow-sm">
                        Simpan & Jadwalkan
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>

