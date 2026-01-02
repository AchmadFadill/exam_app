<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Hasil Ujian</h1>
            <p class="text-sm text-gray-500">Lihat statistik dan hasil ujian per kelas</p>
        </div>
    </div>

    <!-- Results List -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-medium">
                    <tr>
                        <th class="px-6 py-4">Nama Ujian</th>
                        <th class="px-6 py-4">Kelas</th>
                        <th class="px-6 py-4">Mata Pelajaran</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4 text-center">Peserta</th>
                        <th class="px-6 py-4 text-center">Rata-rata</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($results as $result)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-900">{{ $result['exam_name'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $result['class'] }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $result['subject'] }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $result['date'] }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $result['participants'] }} Siswa
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-bold {{ $result['avg_score'] >= 75 ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ $result['avg_score'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route($detailRoute, $result['id']) }}" class="inline-flex items-center justify-center p-2 text-gray-500 hover:text-blue-600 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
