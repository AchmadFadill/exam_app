<x-student-layout>
    <x-slot name="title"></x-slot>
<div class="container mx-auto px-6 py-8">
    <div class="mb-8">
        <h3 class="text-gray-700 text-3xl font-medium">Daftar Ujian</h3>
        <p class="text-gray-500 mt-1">Lakukan ujian sesuai dengan jadwal yang telah ditentukan.</p>
    </div>

    <!-- Exam List Table/Grid -->
    <!-- Exam List Table -->
    <x-table>
        <x-table.thead>
            <x-table.tr>
                <x-table.th>Mata Pelajaran</x-table.th>
                <x-table.th>Nama Ujian</x-table.th>
                <x-table.th>Durasi</x-table.th>
                <x-table.th>Status</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </x-table.tr>
        </x-table.thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <!-- Exam 1 -->
            <x-table.tr>
                <x-table.td class="whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="p-2 rounded-xl bg-primary/10 text-primary mr-4 shadow-inner">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="text-sm font-black text-text-main uppercase tracking-tight">Matematika</div>
                    </div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap italic text-text-muted font-bold">
                    Ujian Tengah Semester
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                    <div class="font-black text-xs text-text-main uppercase tracking-widest">90 Menit</div>
                    <div class="text-[10px] text-text-muted font-bold tracking-widest uppercase mt-0.5">08:00 - 09:30</div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                    <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full bg-amber-50 text-amber-600 border border-amber-500/10">
                        Belum Mulai
                    </span>
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-right">
                    <span class="text-[10px] font-black uppercase tracking-widest text-text-muted opacity-40">Belum Dibuka</span>
                </x-table.td>
            </x-table.tr>

            <!-- Exam 2 -->
            <x-table.tr>
                <x-table.td class="whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="p-2 rounded-xl bg-green-500/10 text-green-600 mr-4 shadow-inner">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5h12M9 3v2m1.042 11.35a12.042 12.042 0 01-2.924-4.223 12.083 12.083 0 003.076-4.927M10 13l4 4 4-4m-4 4V7" />
                            </svg>
                        </div>
                        <div class="text-sm font-black text-text-main uppercase tracking-tight">Bahasa Indonesia</div>
                    </div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap italic text-text-muted font-bold">
                    Ulangan Harian 1
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                    <div class="font-black text-xs text-text-main uppercase tracking-widest">60 Menit</div>
                    <div class="text-[10px] text-text-muted font-bold tracking-widest uppercase mt-0.5">10:00 - 11:00</div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                    <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full bg-green-500/10 text-green-600 border border-green-500/20 animate-pulse">
                        Bisa Dikerjakan
                    </span>
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-right">
                    <x-button href="{{ route('student.exam.show', ['id' => 1]) }}" variant="primary" class="text-[10px] px-6">
                        Kerjakan
                    </x-button>
                </x-table.td>
            </x-table.tr>

            <!-- Exam 3 -->
            <x-table.tr class="opacity-60">
                <x-table.td class="whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="p-2 rounded-xl bg-gray-100 dark:bg-slate-800 text-text-muted mr-4 shadow-inner">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="text-sm font-black text-text-muted uppercase tracking-tight">Fisika</div>
                    </div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap italic text-text-muted font-bold">
                    Kuis Fisika Dasar
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                    <div class="font-black text-xs text-text-muted uppercase tracking-widest">45 Menit</div>
                    <div class="text-[10px] text-text-muted font-bold tracking-widest uppercase mt-0.5">13:00 - 13:45</div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                    <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full bg-gray-100 text-text-muted border border-border-subtle">
                        Selesai
                    </span>
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-right">
                    <x-button variant="soft" class="text-[10px] px-6">Hasil</x-button>
                </x-table.td>
            </x-table.tr>
        </tbody>
    </x-table>
</div>
</x-student-layout>
