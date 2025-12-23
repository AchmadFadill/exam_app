@extends('layouts.student')

@section('title', 'Dashboard Siswa')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="mb-8">
        <h3 class="text-gray-700 text-3xl font-medium">Selamat Datang, Siswa!</h3>
        <p class="text-gray-500 mt-1">Berikut adalah daftar ujian yang tersedia untuk Anda.</p>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="flex items-center p-4 bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-3 rounded-full bg-indigo-600 bg-opacity-75 text-white mr-4">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Ujian Tersedia</p>
                <p class="text-lg font-semibold text-gray-700">3</p>
            </div>
        </div>
        <div class="flex items-center p-4 bg-white rounded-xl shadow-sm border border-gray-100">
             <div class="p-3 rounded-full bg-green-500 bg-opacity-75 text-white mr-4">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Selesai Dikerjakan</p>
                <p class="text-lg font-semibold text-gray-700">1</p>
            </div>
        </div>
    </div>

    <!-- Exam List -->
    <h4 class="text-gray-700 text-xl font-medium mb-4">Daftar Ujian</h4>
    
    <div class="grid gap-6 mb-8 md:grid-cols-2 lg:grid-cols-3">
        
        <!-- Exam Card 1: Belum Mulai -->
        <div class="flex flex-col bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden transform transition duration-500 hover:scale-105">
            <div class="bg-indigo-600 h-2"></div>
            <div class="p-6 flex-1 flex flex-col">
                <div class="flex justify-between items-start mb-4">
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded">Matematika</span>
                    <span class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        90 Menit
                    </span>
                </div>
                
                <h3 class="text-xl font-bold text-gray-800 mb-2">Ujian Tengah Semester</h3>
                <p class="text-gray-600 text-sm mb-4">Kelas X - Matematika Wajib</p>
                
                <div class="mt-auto">
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4 rounded-r" role="alert">
                        <p class="text-xs text-yellow-700 font-bold">Belum Mulai</p>
                        <p class="text-xs text-yellow-600 mt-1">
                            Dimulai dalam: 
                            <span class="font-mono font-bold" x-data="{ 
                                time: 3600,
                                get formatted() {
                                    const h = Math.floor(this.time / 3600);
                                    const m = Math.floor((this.time % 3600) / 60);
                                    const s = this.time % 60;
                                    return `${h}j ${m}m ${s}d`;
                                }
                            }" x-init="setInterval(() => time = time > 0 ? time - 1 : 0, 1000)">
                                <span x-text="formatted"></span>
                            </span>
                        </p>
                    </div>
                    <button class="w-full bg-gray-300 text-gray-500 font-bold py-2 px-4 rounded-lg cursor-not-allowed uppercase tracking-wider text-sm" disabled>
                        Belum Dibuka
                    </button>
                </div>
            </div>
        </div>

        <!-- Exam Card 2: Bisa Dikerjakan -->
        <div class="flex flex-col bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden transform transition duration-500 hover:scale-105">
            <div class="bg-green-500 h-2"></div>
            <div class="p-6 flex-1 flex flex-col">
                <div class="flex justify-between items-start mb-4">
                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Bahasa Indonesia</span>
                    <span class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        60 Menit
                    </span>
                </div>
                
                <h3 class="text-xl font-bold text-gray-800 mb-2">Ulangan Harian 1</h3>
                <p class="text-gray-600 text-sm mb-4">Kelas X - Teks Laporan Hasil Observasi</p>
                
                <div class="mt-auto">
                    <div class="bg-green-50 border-l-4 border-green-500 p-3 mb-4 rounded-r" role="alert">
                        <p class="text-xs text-green-700 font-bold">Sedang Berlangsung</p>
                        <p class="text-xs text-green-600 mt-1">Silakan kerjakan sekarang.</p>
                    </div>
                    <a href="{{ route('student.exam.show', ['id' => 1]) }}" class="block w-full text-center bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 uppercase tracking-wider text-sm">
                        Mulai Kerjakan
                    </a>
                </div>
            </div>
        </div>

         <!-- Exam Card 3: Selesai -->
        <div class="flex flex-col bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden transform transition duration-500 hover:scale-105 opacity-75 grayscale hover:grayscale-0 hover:opacity-100">
            <div class="bg-gray-500 h-2"></div>
            <div class="p-6 flex-1 flex flex-col">
                <div class="flex justify-between items-start mb-4">
                    <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded">Fisika</span>
                    <span class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        45 Menit
                    </span>
                </div>
                
                <h3 class="text-xl font-bold text-gray-800 mb-2">Kuis Fisika Dasar</h3>
                <p class="text-gray-600 text-sm mb-4">Kelas X - Besaran dan Satuan</p>
                
                <div class="mt-auto">
                     <div class="bg-gray-50 border-l-4 border-gray-400 p-3 mb-4 rounded-r" role="alert">
                        <p class="text-xs text-gray-700 font-bold">Selesai</p>
                        <p class="text-xs text-gray-600 mt-1">Anda sudah mengerjakan ujian ini.</p>
                    </div>
                    <button class="w-full bg-gray-100 text-gray-400 font-bold py-2 px-4 rounded-lg border border-gray-200 cursor-default uppercase tracking-wider text-sm">
                        Lihat Hasil
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
