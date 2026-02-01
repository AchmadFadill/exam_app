@section('title', 'Laporan Hasil Ujian')

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route($backRoute) }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-text-main">Detail Laporan</h2>
                <p class="text-text-muted text-sm">{{ $exam['exam_name'] }} • {{ $exam['class'] }}</p>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-card variant="stat" title="Rata-rata Nilai" :value="$exam['avg_score']" color="primary">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </x-slot>
        </x-card>
        <x-card variant="stat" title="Nilai Tertinggi" :value="$exam['highest']" color="green">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </x-slot>
        </x-card>
        <x-card variant="stat" title="Nilai Terendah" :value="$exam['lowest']" color="amber">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
            </x-slot>
        </x-card>
        <x-card variant="stat" title="Total Peserta" :value="$exam['participants']" color="indigo">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </x-slot>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Student List -->
        <div class="lg:col-span-2">
            <x-card title="Hasil Per Siswa">
                <!-- Filter Buttons -->
                <div class="flex flex-wrap gap-2 mb-6 -mt-2">
                    <button 
                        wire:click="sortByHighest" 
                        class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest transition-all
                            {{ $sortBy === 'highest' ? 'bg-green-500 text-white shadow-lg shadow-green-500/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                        🏆 Tertinggi
                    </button>
                    <button 
                        wire:click="sortByLowest" 
                        class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest transition-all
                            {{ $sortBy === 'lowest' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                        📉 Terendah
                    </button>
                    <button 
                        wire:click="sortByFastest" 
                        class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest transition-all
                            {{ $sortBy === 'fastest' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                        ⚡ Tercepat
                    </button>
                    <button 
                        wire:click="sortBySlowest" 
                        class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest transition-all
                            {{ $sortBy === 'slowest' ? 'bg-purple-500 text-white shadow-lg shadow-purple-500/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                        🐌 Terlambat
                    </button>
                </div>

                <div class="overflow-x-auto -mx-6 -my-6">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-500 font-medium">
                            <tr>
                                <th class="px-6 py-4">Nama Siswa</th>
                                <th class="px-6 py-4 text-center">Nilai</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($students as $student)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $student['name'] }}</div>
                                    <div class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5">{{ $student['started_at'] }} - {{ $student['submitted_at'] }}</div>
                                </td>
                                <td class="px-6 py-4 text-center font-black text-lg text-gray-900">{{ $student['score'] }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                        {{ $student['status'] == 'Lulus' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $student['status'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <x-button variant="soft" class="px-3 py-1 text-[10px]">Detail</x-button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Most Failed Questions (Teacher Only maybe, or shared) -->
        <div class="lg:col-span-1">
            <x-card title="Analisis Soal">
                <p class="text-xs text-text-muted mb-4 uppercase tracking-widest font-bold">Soal Paling Banyak Salah</p>
                <div class="space-y-4">
                    @foreach($most_failed_questions as $q)
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 group hover:border-red-100 transition-colors">
                        <div class="flex justify-between items-start mb-2">
                            <span class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center font-bold text-sm text-gray-900">#{{ $q['number'] }}</span>
                            <span class="text-xs font-black text-red-600 uppercase tracking-widest">{{ $q['failed_percentage'] }}% Gagal</span>
                        </div>
                        <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed mb-3">{{ $q['text'] }}</p>
                        <div class="pt-3 border-t border-gray-200/50 text-[10px] text-gray-500">
                             Jawaban Benar: <span class="font-bold text-green-600">{{ $q['correct_answer'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <x-button variant="primary" class="w-full mt-6">Lihat Analisis Lengkap</x-button>
            </x-card>
        </div>
    </div>
</div>
