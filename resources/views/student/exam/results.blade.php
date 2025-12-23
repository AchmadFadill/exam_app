@extends('layouts.student')

@section('title', 'Hasil Ujian')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="mb-8">
        <h3 class="text-gray-700 text-3xl font-medium">Riwayat Hasil Ujian</h3>
        <p class="text-gray-500 mt-1">Pantau perkembangan belajarmu di sini.</p>
    </div>

    <!-- Results History Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Ujian</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Result 1: Published -->
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                             <div class="flex items-center">
                                <div class="p-2 rounded bg-blue-100 text-blue-700 mr-3">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div class="text-sm font-semibold text-gray-900">Bahasa Indonesia</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Ulangan Harian 1
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            23 Des 2025, 09:30
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-lg font-bold text-green-600">85.0</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('student.results.detail', ['id' => 1]) }}" class="text-indigo-600 hover:text-indigo-900 font-bold uppercase tracking-wider text-xs">Lihat Detail</a>
                        </td>
                    </tr>

                    <!-- Result 2: Waiting -->
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                             <div class="flex items-center">
                                <div class="p-2 rounded bg-purple-100 text-purple-700 mr-3">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="text-sm font-semibold text-gray-900">Matematika</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Latihan Persiapan UN
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            21 Des 2025, 14:15
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-700 italic">
                                Belum Terbit
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-gray-400 cursor-not-allowed font-bold uppercase tracking-wider text-xs" disabled>Lihat Detail</button>
                        </td>
                    </tr>

                     <!-- Result 3: Published -->
                     <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                             <div class="flex items-center">
                                <div class="p-2 rounded bg-yellow-100 text-yellow-700 mr-3">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5a2 2 0 00.586-1.414V5L8 4z" />
                                    </svg>
                                </div>
                                <div class="text-sm font-semibold text-gray-900">Kimia</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Kuis Termokimia
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            15 Des 2025, 10:00
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                             <span class="text-lg font-bold text-green-600">92.5</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="text-indigo-600 hover:text-indigo-900 font-bold uppercase tracking-wider text-xs">Lihat Detail</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
