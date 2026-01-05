@section('title', 'Bank Soal')

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-bold text-2xl text-text-main">Bank Soal</h2>
            <p class="text-text-muted text-sm">Kelola database soal untuk ujian</p>
        </div>
        <div>
            <button wire:click="openImportModal" class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-text-main px-4 py-2 rounded-lg text-sm font-medium transition-colors mr-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Import Excel
            </button>
            <a href="{{ route('teacher.question-bank.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat Soal Baru
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

    <!-- Import Modal -->
    @if($showImportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showImportModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-text-main">Import Soal dari Excel</h3>
                <button wire:click="$set('showImportModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Step 1: Download Template -->
                <div class="bg-blue-50 p-4 rounded-lg flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <h4 class="text-sm font-bold text-blue-800">Langkah 1: Download Template</h4>
                        <p class="text-xs text-blue-700 mt-1">Gunakan template ini untuk mengisi soal agar formatnya sesuai.</p>
                        <button wire:click="downloadTemplate" class="mt-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded transition-colors inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download Template Excel
                        </button>
                    </div>
                </div>

                <!-- Step 2: Upload -->
                <div>
                     <label class="block text-sm font-medium text-text-main mb-2">Langkah 2: Upload File Excel</label>
                     <div class="flex items-center justify-center w-full">
                        <label for="import-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span></p>
                                <p class="text-xs text-gray-500">XLSX, XLS (Max. 2MB)</p>
                            </div>
                            <input id="import-file" type="file" class="hidden" wire:model="importFile" />
                        </label>
                    </div>
                    @if($importFile)
                        <div class="mt-2 text-sm text-green-600 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            File terpilih: {{ $importFile->getClientOriginalName() }}
                        </div>
                    @endif
                    @error('importFile') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="p-6 bg-gray-50 flex justify-end gap-3 border-t border-gray-100">
                <button wire:click="$set('showImportModal', false)" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-100 font-medium transition-colors">Batal</button>
                <button wire:click="importQuestions" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" @if(!$importFile) disabled @endif>
                    Mulai Import
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
