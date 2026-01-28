@section('title', $examId ? 'Edit Ujian'  : 'Buat Ujian Baru')

<div class="max-w-6xl mx-auto space-y-8">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('teacher.exams.index') }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h2 class="font-black text-3xl text-text-main tracking-tight">
            {{ $examId ? 'Edit Ujian' : 'Buat Ujian Baru' }}
        </h2>
    </div>

    {{-- Step Indicator --}}
    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between max-w-3xl mx-auto">
            @foreach([
                ['num' => 1, 'title' => 'Info Dasar'],
                ['num' => 2, 'title' => 'Pilih Soal'],
                ['num' => 3, 'title' => 'Pengaturan'],
                ['num' => 4, 'title' => 'Review']
            ] as $stepInfo)
                <div class="flex flex-col items-center flex-1">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg transition-all {{ $currentStep >= $stepInfo['num'] ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'bg-gray-200 text-gray-500' }}">
                        {{ $stepInfo['num'] }}
                    </div>
                    <span class="text-xs font-semibold mt-2 {{ $currentStep >= $stepInfo['num'] ? 'text-primary' : 'text-gray-400' }}">
                        {{ $stepInfo['title'] }}
                    </span>
                </div>
                @if($stepInfo['num'] < 4)
                    <div class="flex-1 h-1 {{ $currentStep > $stepInfo['num'] ? 'bg-primary' : 'bg-gray-200' }} mx-2 rounded-full"></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Form Steps --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        

        {{-- Step 1: Basic Information --}}
        @if($currentStep === 1)
        <div class="p-8 space-y-6">
            <h3 class="text-xl font-bold text-text-main mb-6">Informasi Dasar Ujian</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ujian <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Contoh: Ujian Harian Matematika Bab 1">
                    @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mata Pelajaran <span class="text-red-500">*</span></label>
                    <select wire:model="subject_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="">Pilih Mata Pelajaran</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    @error('date') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Mulai <span class="text-red-500">*</span></label>
                    <input type="time" wire:model="start_time" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    @error('start_time') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Selesai <span class="text-red-500">*</span></label>
                    <input type="time" wire:model="end_time" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    @error('end_time') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durasi (Menit) <span class="text-red-500">*</span></label>
                    <input type="number" wire:model="duration_minutes" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" min="10" max="300">
                    @error('duration_minutes') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Lulus <span class="text-red-500">*</span></label>
                    <input type="number" wire:model="passing_grade" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" min="0" max="100">
                    @error('passing_grade') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Skor Default Per Soal <span class="text-red-500">*</span></label>
                    <input type="number" wire:model="default_score" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" min="1">
                    @error('default_score') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
        @endif

        {{-- Step 2: Question Selection --}}
        @if($currentStep === 2)
        <div class="p-8 space-y-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-text-main">Pilih Soal dari Bank Soal</h3>
                <div class="flex items-center gap-4">
                    <span class="text-sm font-semibold text-primary">{{ count($selectedQuestions) }} Soal Dipilih</span>
                    @php
                        $totalScore = array_sum($questionScores);
                    @endphp
                    @if(count($selectedQuestions) > 0)
                        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-lg font-bold text-sm">
                            Total Skor: {{ $totalScore }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Search and Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <input type="text" wire:model.live="searchQuery" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary" placeholder="Cari soal...">
                
                <select wire:model.live="filterSubject" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterType" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    <option value="">Semua Tipe</option>
                    <option value="multiple_choice">Pilihan Ganda</option>
                    <option value="essay">Essay</option>
                </select>
            </div>

            {{-- Question Groups List --}}
            <div class="space-y-4 max-h-[500px] overflow-y-auto">
                @forelse($questionGroups as $group)
                    @php
                        // Get all questions for this group
                        $groupQuestions = \App\Models\Question::where('title', $group->title)->get();
                        $groupQuestionIds = $groupQuestions->pluck('id')->toArray();
                        // Check if all questions in this group are selected
                        $allSelected = count(array_intersect($groupQuestionIds, $selectedQuestions)) === count($groupQuestionIds);
                        $someSelected = count(array_intersect($groupQuestionIds, $selectedQuestions)) > 0;
                        $groupScore = 0;
                        foreach ($groupQuestionIds as $qid) {
                            $groupScore += $questionScores[$qid] ?? 0;
                        }
                    @endphp
                    
                    <div class="border-2 rounded-xl overflow-hidden transition-all {{ $allSelected ? 'border-primary bg-blue-50/30' : ($someSelected ? 'border-blue-300 bg-blue-50/20' : 'border-gray-200') }}" 
                         x-data="{ open: {{ $someSelected ? 'true' : 'false' }} }">
                        
                        {{-- Group Header --}}
                        <div class="p-5 cursor-pointer hover:bg-gray-50/50" 
                             @click="if (!{{ $someSelected ? 'true' : 'false' }}) $wire.toggleQuestionGroup('{{ $group->title }}')">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-start gap-3 flex-1">
                                    <input type="checkbox" 
                                           @checked($allSelected)
                                           wire:click.stop="toggleQuestionGroup('{{ $group->title }}')"
                                           class="mt-1 w-5 h-5 rounded text-primary focus:ring-primary">
                                    
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-900 mb-1">{{ $group->title }}</h4>
                                        <p class="text-sm text-gray-600">{{ $group->subject->name }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    @if($someSelected)
                                        <span class="px-3 py-1 bg-primary text-white rounded-lg text-xs font-bold">
                                            Skor: {{ $groupScore }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary">
                                        {{ $group->question_count }} Soal
                                    </span>
                                    @if($someSelected)
                                        <button type="button" @click.stop="open = !open" class="p-1 hover:bg-gray-200 rounded">
                                            <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            @if(!$someSelected)
                                <div class="text-xs text-gray-500 ml-8">
                                    Klik untuk memilih semua soal dalam grup ini
                                </div>
                            @endif
                        </div>

                        {{-- Expandable Question Details --}}
                        @if($someSelected)
                            <div x-show="open" x-collapse class="border-t border-gray-200 bg-white">
                                <div class="p-4 space-y-2">
                                    @foreach($groupQuestions as $question)
                                        @if(in_array($question->id, $selectedQuestions))
                                            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900">{{ Str::limit($question->text, 100) }}</p>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $question->type === 'multiple_choice' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700' }}">
                                                            {{ $question->type === 'multiple_choice' ? 'PG' : 'Essay' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <label class="text-xs font-medium text-gray-600">Skor:</label>
                                                    <input type="number" 
                                                           wire:model.blur="questionScores.{{ $question->id }}" 
                                                           class="w-16 px-2 py-1 border border-gray-300 rounded text-center text-sm font-semibold" 
                                                           min="1"
                                                           max="100">
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p>Tidak ada kelompok soal ditemukan</p>
                    </div>
                @endforelse
            </div>
            @error('selectedQuestions') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        @endif

        {{-- Step 3: Class Assignment & Settings --}}
        @if($currentStep === 3)
        <div class="p-8 space-y-6">
            <h3 class="text-xl font-bold text-text-main mb-6">Pengaturan Kelas & Ujian</h3>

            {{-- Class Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Pilih Kelas <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($classrooms as $classroom)
                        <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all {{ in_array($classroom->id, $selectedClasses) ? 'border-primary bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="checkbox" 
                                   value="{{ $classroom->id }}" 
                                   wire:model="selectedClasses" 
                                   class="w-4 h-4 rounded text-primary focus:ring-primary">
                            <span class="ml-2 text-sm font-medium">{{ $classroom->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('selectedClasses') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Token --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Token Ujian <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <input type="text" wire:model="token" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary font-mono text-lg" maxlength="6" readonly>
                    <button type="button" wire:click="generateToken" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors">
                        Generate Baru
                    </button>
                </div>
                @error('token') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Settings --}}
            <div class="space-y-4">
                <h4 class="font-semibold text-gray-900">Opsi Tambahan</h4>
                
                <label class="flex items-center gap-3">
                    <input type="checkbox" wire:model="shuffle_questions" class="w-5 h-5 rounded text-primary focus:ring-primary">
                    <span class="text-sm font-medium text-gray-700">Acak urutan soal untuk setiap siswa</span>
                </label>

                <label class="flex items-center gap-3">
                    <input type="checkbox" wire:model="shuffle_answers" class="w-5 h-5 rounded text-primary focus:ring-primary">
                    <span class="text-sm font-medium text-gray-700">Acak urutan jawaban (pilihan ganda)</span>
                </label>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Toleransi Pergantian Tab</label>
                    <input type="number" wire:model="tab_tolerance" class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary" min="0" max="10">
                    <p class="mt-1 text-xs text-gray-500">Berapa kali siswa boleh keluar dari halaman ujian</p>
                    @error('tab_tolerance') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
        @endif

        {{-- Step 4: Review & Publish --}}
        @if($currentStep === 4)
        <div class="p-8 space-y-6">
            <h3 class="text-xl font-bold text-text-main mb-6">Review & Publikasi</h3>

            <div class="bg-gray-50 rounded-lg p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Nama Ujian</p>
                        <p class="font-semibold text-gray-900">{{ $name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Mata Pelajaran</p>
                        <p class="font-semibold text-gray-900">{{ $subjects->find($subject_id)?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal & Waktu</p>
                        <p class="font-semibold text-gray-900">{{ date('d M Y', strtotime($date)) }}, {{ $start_time }} - {{ $end_time }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Durasi</p>
                        <p class="font-semibold text-gray-900">{{ $duration_minutes }} Menit</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Jumlah Soal</p>
                        <p class="font-semibold text-gray-900">{{ count($selectedQuestions) }} Soal</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Skor Ujian</p>
                        <p class="font-bold text-green-600 text-lg">{{ array_sum($questionScores) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Token</p>
                        <p class="font-semibold text-gray-900 font-mono">{{ $token }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Kelas</p>
                        <p class="font-semibold text-gray-900">{{ count($selectedClasses) }} Kelas</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Nilai Lulus</p>
                        <p class="font-semibold text-gray-900">{{ $passing_grade }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <label class="flex-1">
                    <input type="radio" wire:model="status" value="draft" class="mr-2">
                    <span class="font-medium">Simpan sebagai Draft</span>
                    <p class="text-sm text-gray-500 ml-6">Ujian belum bisa diakses siswa</p>
                </label>
                <label class="flex-1">
                    <input type="radio" wire:model="status" value="scheduled" class="mr-2">
                    <span class="font-medium">Jadwalkan</span>
                    <p class="text-sm text-gray-500 ml-6">Siswa bisa akses sesuai jadwal</p>
                </label>
            </div>
        </div>
        @endif

        {{-- Navigation Buttons --}}
        <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex justify-between">
            @if($currentStep > 1)
                <button type="button" wire:click="previousStep" class="px-6 py-3 bg-white border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                    ← Sebelumnya
                </button>
            @else
                <div></div>
            @endif

            @if($currentStep < 4)
                <button type="button" wire:click="nextStep" class="px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    Lanjut →
                </button>
            @else
                <div class="flex gap-3">
                    <button type="button" wire:click="saveDraft" class="px-6 py-3 bg-white border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                        Simpan Draft
                    </button>
                    <button type="button" wire:click="publish" class="px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                        Publikasi Ujian
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
