@extends('layouts.student')

@section('title', 'Detail Hasil Ujian')

@section('content')
<div class="container mx-auto px-6 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('student.results') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7"></path></svg>
            Kembali ke Riwayat
        </a>
    </div>

    <!-- Result Header Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-10 text-white">
            <div class="md:flex justify-between items-center">
                <div class="mb-6 md:mb-0">
                    <h2 class="text-3xl font-bold mb-2">Ulangan Harian 1</h2>
                    <p class="text-blue-100 text-lg">Bahasa Indonesia - Kelas X</p>
                    <div class="flex items-center mt-4 text-blue-100 text-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Dikerjakan pada 23 Desember 2025, 09:00 - 09:30
                    </div>
                </div>
                <div class="bg-white/20 backdrop-blur-md rounded-2xl p-6 text-center border border-white/30">
                    <div class="text-sm font-medium uppercase tracking-wider mb-1">Nilai Akhir</div>
                    <div class="text-6xl font-black">85.0</div>
                    <div class="mt-2 text-xs font-semibold px-3 py-1 bg-green-500 rounded-full inline-block">LULUS KKM</div>
                </div>
            </div>
        </div>
        
        <!-- Stats Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-gray-100 border-t border-gray-100">
            <div class="p-6 text-center">
                <div class="text-sm font-medium text-gray-500 mb-1 leading-tight">Total Soal</div>
                <div class="text-2xl font-bold text-gray-800">20</div>
            </div>
            <div class="p-6 text-center">
                <div class="text-sm font-medium text-green-600 mb-1 leading-tight">Benar</div>
                <div class="text-2xl font-bold text-green-600">17</div>
            </div>
            <div class="p-6 text-center">
                <div class="text-sm font-medium text-red-600 mb-1 leading-tight">Salah</div>
                <div class="text-2xl font-bold text-red-600">3</div>
            </div>
            <div class="p-6 text-center">
                <div class="text-sm font-medium text-gray-400 mb-1 leading-tight">Kosong</div>
                <div class="text-2xl font-bold text-gray-400">0</div>
            </div>
        </div>
    </div>

    <!-- Discussion Section -->
    <div class="mb-6 flex items-center justify-between">
        <h4 class="text-gray-800 text-xl font-bold">Pembahasan Jawaban</h4>
        <div class="text-sm text-gray-500">Menampilkan 1-10 dari 20 soal</div>
    </div>

    <div class="space-y-6">
        <!-- Question Item 1 (Correct) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-bold text-gray-400">SOAL NO. 1</span>
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        BENAR
                    </span>
                </div>
                <div class="prose max-w-none text-gray-800 mb-4">
                    <p>Kalimat berikut yang merupakan contoh kalimat objektif dalam teks laporan hasil observasi adalah...</p>
                </div>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="text-sm">
                        <div class="font-semibold text-gray-500 mb-2">Jawaban Kamu:</div>
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-blue-700 font-medium">
                             C. Harimau merupakan hewan karnivora yang memakan daging.
                        </div>
                    </div>
                     <div class="text-sm">
                        <div class="font-semibold text-gray-500 mb-2">Kunci Jawaban:</div>
                        <div class="p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 font-medium">
                             C. Harimau merupakan hewan karnivora yang memakan daging.
                        </div>
                    </div>
                </div>

                <!-- Teacher Explanation -->
                <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <div class="flex items-center mb-2">
                         <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-2">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                         </div>
                         <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Catatan Dari  Guru</span>
                    </div>
                    <p class="text-sm text-gray-600">
                        Kalimat objektif adalah kalimat yang mengungkapkan fakta tanpa dipengaruhi pendapat pribadi. Opsi C adalah fakta biologis yang dapat dibuktikan secara nyata.
                    </p>
                </div>
            </div>
        </div>

        <!-- Question Item 2 (Wrong) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-bold text-gray-400">SOAL NO. 2</span>
                    <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        SALAH
                    </span>
                </div>
                <div class="prose max-w-none text-gray-800 mb-4">
                    <p>Fungsi dari teks laporan hasil observasi adalah...</p>
                </div>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="text-sm">
                        <div class="font-semibold text-gray-500 mb-2">Jawaban Kamu:</div>
                        <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 font-medium">
                             A. Menghibur pembaca dengan cerita unik.
                        </div>
                    </div>
                     <div class="text-sm">
                        <div class="font-semibold text-gray-500 mb-2">Kunci Jawaban:</div>
                        <div class="p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 font-medium">
                             D. Menyajikan informasi objektif tentang suatu hal.
                        </div>
                    </div>
                </div>

                <!-- Teacher Explanation -->
                <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <div class="flex items-center mb-2">
                          <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-2">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                         </div>
                         <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Catatan Dari  Guru</span>
                    </div>
                    <p class="text-sm text-gray-600">
                        Teks laporan hasil observasi bersifat informatif, komunikatif, dan objektif. Tujuannya adalah memberikan informasi tentang suatu benda, hewan, atau fenomena berdasarkan fakta pengamatan.
                    </p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
