@section('title', 'Bank Soal')

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-bold text-2xl text-text-main">Bank Soal</h2>
            <p class="text-text-muted text-sm">Kelola database soal untuk ujian</p>
        </div>
        <div>
            <a href="{{ route('teacher.question-bank.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Soal
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-bg-surface rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary sm:text-sm" placeholder="Cari pertanyaan soal...">
                </div>
            </div>
            <div>
                <select wire:model.live="subjectFilter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-200 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md text-text-main">
                    <option value="">Semua Mata Pelajaran</option>
                    <option value="Matematika">Matematika</option>
                    <option value="Biologi">Biologi</option>
                    <option value="Sejarah">Sejarah</option>
                    <option value="Geografi">Geografi</option>
                </select>
            </div>
            <div>
                <select wire:model.live="typeFilter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-200 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md text-text-main">
                    <option value="">Semua Tipe Soal</option>
                    <option value="Pilihan Ganda">Pilihan Ganda</option>
                    <option value="Essay">Essay</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Question List -->
    <div class="bg-bg-surface rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Soal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Mata Pelajaran</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Tipe</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Tanggal Dibuat</th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-bg-surface divide-y divide-gray-200">
                    @forelse($questions as $question)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-text-main line-clamp-2 max-w-md">{{ $question['q'] }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-primary">
                                {{ $question['subject'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-muted">{{ $question['type'] }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                            {{ $question['created_at'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('teacher.question-bank.edit', $question['id']) }}" class="text-primary hover:text-blue-800">Edit</a>
                                <button type="button" class="text-red-600 hover:text-red-900">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-text-muted">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p>Belum ada soal ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            <!-- Pagination Placeholder -->
            <div class="text-sm text-text-muted">Menampilkan {{ count($questions) }} data</div>
        </div>
    </div>
</div>
