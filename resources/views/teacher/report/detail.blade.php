<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('teacher.reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    &larr; Kembali
                </a>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $exam['exam_name'] }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $exam['class'] }} &bull; {{ $exam['subject'] }} &bull; {{ $exam['date'] }}
            </p>
        </div>
        <button type="button" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Excel
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Partisipan</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $exam['participants'] }}</p>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Rata-rata</p>
            <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $exam['avg_score'] }}</p>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nilai Tertinggi</p>
            <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">{{ $exam['highest'] }}</p>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nilai Terendah</p>
            <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ $exam['lowest'] }}</p>
        </div>
    </div>

    <!-- Question Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Soal Paling Banyak Salah
            </h3>
            <div class="space-y-4">
                @foreach($most_failed_questions as $q)
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center font-bold text-sm">
                                {{ $q['number'] }}
                            </span>
                            <p class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2">{{ $q['text'] }}</p>
                        </div>
                        <span class="text-xs font-bold text-red-600 dark:text-red-400">
                            {{ $q['failed_percentage'] }}% Salah
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 mb-3">
                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ $q['failed_percentage'] }}%"></div>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-500 dark:text-gray-400">Dijawab salah oleh <span class="font-bold text-gray-700 dark:text-gray-200">{{ $q['failed_count'] }} siswa</span></span>
                        <span class="text-green-600 dark:text-green-400 font-medium">Kunci: {{ $q['correct_answer'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Statistik Kelulusan</h3>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col items-center justify-center">
                <div class="relative w-32 h-32 mb-4">
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="12" fill="transparent" class="text-gray-100 dark:text-gray-700" />
                        <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="12" fill="transparent" stroke-dasharray="364.4" stroke-dashoffset="{{ 364.4 * (1 - 0.85) }}" class="text-green-500" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-extrabold text-gray-900 dark:text-white">85%</span>
                        <span class="text-[10px] text-gray-500 uppercase font-bold">Lulus</span>
                    </div>
                </div>
                <div class="w-full space-y-2">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Lulus (Tuntas)</span>
                        <span class="font-bold text-green-600">27 Siswa</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Remedial</span>
                        <span class="font-bold text-red-600">5 Siswa</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="text-lg font-bold text-gray-900 dark:text-white pt-4">Daftar Nilai Siswa</h3>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Nama Siswa</th>
                        <th class="px-6 py-4 text-center">Waktu Mulai</th>
                        <th class="px-6 py-4 text-center">Waktu Submit</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($students as $index => $student)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $student['name'] }}</td>
                        <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-300 font-mono text-xs">{{ $student['started_at'] }}</td>
                        <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-300 font-mono text-xs">{{ $student['submitted_at'] }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $student['status'] === 'Lulus' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                {{ $student['status'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-bold text-base text-gray-900 dark:text-white">{{ $student['score'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
