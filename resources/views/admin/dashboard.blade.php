@extends('layouts.admin')

@section('title', 'Dashboard ')

@section('content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Stat Card 1 -->
        <x-card variant="stat" title="Total Siswa" value="452" subtitle="+12 Siswa baru semester ini" color="primary">
             <x-slot name="icon">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
             </x-slot>
        </x-card>

        <x-card variant="stat" title="Guru Aktif" value="34" subtitle="Semua terdaftar" color="secondary">
             <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
             </x-slot>
        </x-card>

        <x-card variant="stat" title="Ujian Selesai" value="128" subtitle="Minggu ini" color="green">
             <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
             </x-slot>
        </x-card>

        <x-card variant="stat" title="Sedang Ujian" value="2 Kelas" subtitle="X IPA 1, XI IPS 2" color="red">
             <x-slot name="icon">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
             </x-slot>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Live Monitoring Section -->
        <x-card title="Monitoring Ujian (Live)" class="lg:col-span-2">
            <x-slot name="header_actions">
                <span class="px-3 py-1 bg-red-100 text-red-600 rounded-full text-xs font-semibold animate-pulse">Live Updates</span>
            </x-slot>

            <div class="divide-y divide-gray-50 -mx-6 -my-6">
                <!-- Ujian Item 1 -->
                <div class="flex items-center justify-between p-5 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="h-10 w-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center font-bold">
                            MTK
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Matematika Wajib - X IPA 1</h4>
                            <p class="text-xs text-gray-500">Bu Ani • 08:00 - 09:30 WIB</p>
                        </div>
                    </div>
                    <div class="text-right">
                            <div class="text-sm font-bold text-gray-800">32/34</div>
                            <div class="text-xs text-gray-500">Siswa Online</div>
                    </div>
                </div>

                    <!-- Ujian Item 2 -->
                <div class="flex items-center justify-between p-5 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="h-10 w-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center font-bold">
                            BIO
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Biologi - XI IPS 2</h4>
                            <p class="text-xs text-gray-500">Pak Budi • 08:00 - 09:30 WIB</p>
                        </div>
                    </div>
                    <div class="text-right">
                            <div class="text-sm font-bold text-gray-800">28/30</div>
                            <div class="text-xs text-gray-500">Siswa Online</div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <h4 class="text-sm font-semibold text-gray-600 mb-3">Aktivitas Mencurigakan Terakhir</h4>
                <div class="space-y-2">
                    <div class="flex items-start p-2 hover:bg-red-50 rounded transition-colors group">
                        <span class="inline-block h-2 w-2 mt-1.5 rounded-full bg-red-500 mr-2"></span>
                        <div class="flex-1">
                            <p class="text-sm text-gray-800"><span class="font-semibold">Ahmad Fulan</span> (X IPA 1) terdeteksi pindah tab.</p>
                            <p class="text-xs text-gray-500">2 menit yang lalu</p>
                        </div>
                        <button class="text-xs text-blue-600 hover:underline opacity-0 group-hover:opacity-100 transition-opacity">Detail</button>
                    </div>
                    <div class="flex items-start p-2 hover:bg-amber-50 rounded transition-colors group">
                        <span class="inline-block h-2 w-2 mt-1.5 rounded-full bg-amber-500 mr-2"></span>
                        <div class="flex-1">
                            <p class="text-sm text-gray-800"><span class="font-semibold">Siti Aminah</span> (XI IPS 2) keluar dari mode fullscreen.</p>
                            <p class="text-xs text-gray-500">5 menit yang lalu</p>
                        </div>
                        <button class="text-xs text-blue-600 hover:underline opacity-0 group-hover:opacity-100 transition-opacity">Detail</button>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Quick Actions & Info -->
        <div class="space-y-6">
            <x-card title="Aksi Cepat">
                <div class="grid grid-cols-2 gap-4">
                    <button class="p-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors flex flex-col items-center justify-center text-center">
                        <svg class="h-6 w-6 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span class="text-xs font-semibold">Buat Ujian</span>
                    </button>
                     <button class="p-3 bg-amber-50 text-amber-700 rounded-lg hover:bg-amber-100 transition-colors flex flex-col items-center justify-center text-center">
                        <svg class="h-6 w-6 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <span class="text-xs font-semibold">Tambah Siswa</span>
                    </button>
                </div>
            </x-card>

            <div class="bg-primary rounded-xl shadow-sm p-6 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="font-bold text-lg mb-1">SMAIT Baitul Muslim</h3>
                    <p class="text-blue-200 text-sm mb-4">Tahun Ajaran 2024/2025 - Ganjil</p>
                    <button class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded transition-colors">
                        Pengaturan Sekolah
                    </button>
                </div>
                 <!-- Decorative circle -->
                <div class="absolute -bottom-6 -right-6 h-24 w-24 bg-white/10 rounded-full"></div>
                <div class="absolute top-4 right-4 h-10 w-10 bg-secondary/20 rounded-full blur-xl"></div>
            </div>
        </div>
    </div>
@endsection
