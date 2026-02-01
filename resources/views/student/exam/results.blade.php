<x-student-layout>
    <x-slot name="title">Hasil Ujian</x-slot>
<div class="container mx-auto px-6 py-8">
    <div class="mb-8">
        <h3 class="text-text-main text-3xl font-medium">Riwayat Hasil Ujian</h3>
        <p class="text-text-muted mt-1">Pantau perkembangan belajarmu di sini.</p>
    </div>

    <!-- Results History Table -->
    <!-- Results History Table -->
    <x-table>
        <x-table.thead>
            <x-table.tr>
                <x-table.th>Mata Pelajaran</x-table.th>
                <x-table.th>Nama Ujian</x-table.th>
                <x-table.th>Tanggal Selesai</x-table.th>
                <x-table.th>Nilai</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </x-table.tr>
        </x-table.thead>
        <tbody class="bg-bg-surface dark:bg-bg-surface divide-y divide-border-subtle dark:divide-border-subtle">
            <!-- Result 1: Published -->
            <x-table.tr>
                <x-table.td class="whitespace-nowrap">
                     <div class="flex items-center">
                        <div class="p-2 rounded-xl bg-primary/10 text-primary mr-4 shadow-inner">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div class="text-sm font-black text-text-main uppercase tracking-tight">Bahasa Indonesia</div>
                    </div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap italic text-text-muted font-bold">
                    Ulangan Harian 1
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-[10px] font-black text-text-muted uppercase tracking-widest">
                    23 Des 2025, 09:30
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                    <span class="text-xl font-black text-green-600">85.0</span>
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-right">
                    <x-button href="{{ route('student.results.detail', ['id' => 1]) }}" variant="soft" class="text-[10px] px-6">Lihat Detail</x-button>
                </x-table.td>
            </x-table.tr>

            <!-- Result 2: Waiting -->
            <x-table.tr>
                <x-table.td class="whitespace-nowrap">
                     <div class="flex items-center">
                        <div class="p-2 rounded-xl bg-purple-100 text-purple-700 mr-4 shadow-inner">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="text-sm font-black text-text-main uppercase tracking-tight">Matematika</div>
                    </div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap italic text-text-muted font-bold">
                    Latihan Persiapan UN
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-[10px] font-black text-text-muted uppercase tracking-widest">
                    21 Des 2025, 14:15
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                    <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full bg-amber-500/10 text-amber-600 italic border border-amber-500/20">
                        Belum Terbit
                    </span>
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-right">
                    <span class="text-[10px] font-black uppercase tracking-widest text-text-muted opacity-40">Tunggu</span>
                </x-table.td>
            </x-table.tr>

             <!-- Result 3: Published -->
             <x-table.tr>
                <x-table.td class="whitespace-nowrap">
                     <div class="flex items-center">
                        <div class="p-2 rounded-xl bg-amber-500/10 text-amber-600 mr-4 shadow-inner">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5a2 2 0 00.586-1.414V5L8 4z" />
                            </svg>
                        </div>
                        <div class="text-sm font-black text-text-main uppercase tracking-tight">Kimia</div>
                    </div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap italic text-text-muted font-bold">
                    Kuis Termokimia
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-[10px] font-black text-text-muted uppercase tracking-widest">
                    15 Des 2025, 10:00
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                     <span class="text-xl font-black text-green-600">92.5</span>
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-right">
                    <x-button href="#" variant="soft" class="text-[10px] px-6">Lihat Detail</x-button>
                </x-table.td>
            </x-table.tr>
        </tbody>
    </x-table>
</div>
</x-student-layout>
