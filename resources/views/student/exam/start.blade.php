@extends('layouts.student')

@section('title', 'Konfirmasi Ujian')

@section('content')
<div class="container mx-auto px-6 py-12 max-w-4xl">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Exam Info -->  
        <div class="space-y-6">
            <div>
                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">Ujian Aktif</span>
                <h2 class="mt-4 text-3xl font-extrabold text-gray-900">Matematika Dasar</h2>
                <p class="text-gray-500 mt-2">Ujian Tengah Semester Ganjil TA 2025/2026</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <span class="text-gray-500 text-sm">Kelas</span>
                    <span class="font-bold text-gray-800">X IPA 1</span>
                </div>
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <span class="text-gray-500 text-sm">Waktu</span>
                    <span class="font-bold text-gray-800">90 Menit</span>
                </div>
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <span class="text-gray-500 text-sm">Jumlah Soal</span>
                    <span class="font-bold text-gray-800">40 PG / 5 Essay</span>
                </div>
                 <div class="flex items-center justify-between">
                    <span class="text-gray-500 text-sm">Guru Pengampu</span>
                    <span class="font-bold text-gray-800">Budi Santoso, S.Pd</span>
                </div>
            </div>
        </div>

        <!-- Token & Start -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 flex flex-col justify-center">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Masukkan Token Ujian</h3>
            
            <form action="{{ route('student.exam.show', ['id' => 1]) }}" method="GET" class="space-y-6" x-data="{ token: '' }">
                <div>
                    <label for="token" class="sr-only">Token</label>
                    <input type="text" name="token" id="token" x-model="token" 
                        class="block w-full text-center text-3xl font-mono font-bold tracking-[0.5em] border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 uppercase placeholder-gray-300 py-4" 
                        placeholder="TOKEN" maxlength="6" required>
                    <p class="mt-2 text-center text-sm text-gray-500">Minta token kepada pengawas ujian</p>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Peraturan Ujian</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Waktu berjalan mundur segera setelah tombol mulai diklik.</li>
                                    <li>Dilarang pindah tab/browser (Terdeteksi).</li>
                                    <li>Jangan merefresh halaman ujian.</li>
                                    <li>Pastikan koneksi internet stabil.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" 
                    :disabled="token.length < 5"
                    :class="{'opacity-50 cursor-not-allowed': token.length < 5, 'hover:scale-[1.02]': token.length >= 5}"
                    class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-lg font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform duration-200">
                    MULAI MENGERJAKAN
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
