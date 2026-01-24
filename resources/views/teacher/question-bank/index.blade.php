@section('title', 'Bank Soal')

<div class="space-y-6">
    <div class="mb-12 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6">
        <div>
            <h2 class="text-4xl font-black text-text-main tracking-tight uppercase">Knowledge Base</h2>
            <p class="text-text-muted mt-2 font-bold tracking-widest text-[10px] uppercase opacity-60">Question Repository Management</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="openImportModal" class="group inline-flex items-center gap-3 bg-bg-surface dark:bg-slate-800 border border-border-main dark:border-slate-700 text-text-main px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all hover:bg-gray-50 shadow-sm">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Import Matrix
            </button>
            <a href="{{ route('teacher.question-bank.create') }}" class="group inline-flex items-center gap-3 bg-primary hover:bg-blue-700 text-white px-8 py-3.5 rounded-[2rem] text-sm font-black transition-all shadow-xl shadow-primary/20 uppercase tracking-widest">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Deploy New Item
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-bg-surface dark:bg-slate-800/50 rounded-[2rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main p-8 mb-10 transition-all group focus-within:border-primary/30">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="md:col-span-2">
                <label class="block text-[10px] font-black text-text-muted uppercase tracking-[0.2em] mb-3 opacity-60">Search Parameters</label>
                <div class="relative group/input">
                    <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none text-text-muted group-focus-within/input:text-primary transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live="search" type="text" class="block w-full pl-14 pr-6 py-4 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-slate-700 rounded-2xl font-bold text-sm focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all outline-none" placeholder="Cari konten atau metadata soal...">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-black text-text-muted uppercase tracking-[0.2em] mb-3 opacity-60">Subject Discipline</label>
                <select wire:model.live="subjectFilter" class="block w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-slate-700 rounded-2xl font-bold text-sm focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none outline-none bg-no-repeat bg-[right_1.5rem_center] bg-[length:1em_1em]" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22currentColor%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222.5%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E')">
                    <option value="">Semua Disiplin</option>
                    <option value="Matematika">Matematika</option>
                    <option value="Biologi">Biologi</option>
                    <option value="Sejarah">Sejarah</option>
                    <option value="Geografi">Geografi</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black text-text-muted uppercase tracking-[0.2em] mb-3 opacity-60">Taxonomy Type</label>
                <select wire:model.live="typeFilter" class="block w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-slate-700 rounded-2xl font-bold text-sm focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none outline-none bg-no-repeat bg-[right_1.5rem_center] bg-[length:1em_1em]" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22currentColor%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222.5%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E')">
                    <option value="">Semua Format</option>
                    <option value="Pilihan Ganda">Pilihan Ganda</option>
                    <option value="Essay">Terbuka (Essay)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Question List -->
    <x-card class="overflow-hidden">
        <div class="overflow-x-auto -mx-6 -my-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-slate-800/50 border-b border-border-subtle dark:border-border-subtle">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted opacity-60">Narrative Item / Content</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted opacity-60">Discipline</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted opacity-60">Taxonomy</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted opacity-60">Timestamp</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted opacity-60 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                    @forelse($questions as $question)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-800/30 transition-all group">
                        <td class="px-8 py-6">
                            <div class="text-sm font-black text-text-main line-clamp-2 max-w-lg tracking-tight leading-relaxed group-hover:text-primary transition-colors">{{ $question['q'] }}</div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full bg-primary/10 text-primary">
                                {{ $question['subject'] }}
                            </span>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="text-[10px] font-black text-text-muted uppercase tracking-widest opacity-60">{{ $question['type'] }}</div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap text-[10px] font-black text-text-muted uppercase tracking-widest opacity-40">
                            {{ $question['created_at'] }}
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-3 opacity-40 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('teacher.question-bank.edit', $question['id']) }}" class="p-2 text-primary hover:bg-primary/10 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <button type="button" class="p-2 text-red-600 hover:bg-red-500/10 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center text-text-muted font-bold italic opacity-40">
                             Tidak ada data butir soal dalam modul ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-5 border-t border-border-subtle dark:border-slate-800 bg-gray-50/30 dark:bg-slate-900/30">
            <div class="text-[10px] font-black text-text-muted uppercase tracking-[0.2em] opacity-40">Matrix Count: {{ count($questions) }} Operational Items</div>
        </div>
    </x-card>
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
