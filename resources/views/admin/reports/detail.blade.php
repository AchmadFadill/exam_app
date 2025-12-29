<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
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

    <!-- Student List -->
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
