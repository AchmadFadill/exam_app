@section('title', 'Laporan Hasil Ujian')

<div class="space-y-6">
     <div class="flex justify-between items-center">
        <div>
            <h2 class="font-bold text-2xl text-text-main">Laporan Hasil Ujian</h2>
            <p class="text-text-muted text-sm">Analisis dan rekap nilai siswa</p>
        </div>
        <button class="flex items-center gap-2 bg-bg-surface border border-gray-200 hover:bg-gray-50 text-text-main px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Export Semua (Excel)
        </button>
    </div>

    <!-- Results Table -->
    <div class="bg-bg-surface rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Ujian</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Kelas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Rata-rata</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Tertinggi / Terendah</th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-bg-surface divide-y divide-gray-200">
                    @foreach($results as $result)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-text-main">{{ $result['exam_name'] }}</div>
                             <div class="text-xs text-text-muted">{{ $result['participants'] }} Peserta</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                            {{ $result['class'] }}
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                            {{ $result['date'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 inline-flex text-sm font-bold rounded-lg bg-blue-50 text-primary">
                                {{ $result['avg_score'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                             <div class="flex gap-3">
                                 <span class="text-green-600 flex items-center gap-1">
                                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                                     {{ $result['highest'] }}
                                 </span>
                                 <span class="text-red-500 flex items-center gap-1">
                                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                     {{ $result['lowest'] }}
                                 </span>
                             </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-primary hover:text-blue-800">Detail Analisis</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

