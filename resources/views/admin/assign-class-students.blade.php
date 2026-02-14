<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('admin.classes') }}" variant="secondary" size="sm" square="true" class="!rounded-xl group">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </x-button>
            <div>
                <h2 class="text-xl font-black text-text-main uppercase tracking-tight">Assign Siswa Ke Kelas</h2>
                <p class="text-xs font-bold text-text-muted uppercase tracking-widest mt-1">Kelas: {{ $classroom->name }}</p>
            </div>
        </div>
    </div>

    <x-card>
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <div class="relative w-full sm:max-w-md">
                    <span class="absolute inset-y-0 left-4 flex items-center text-text-muted">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" wire:model.live="search" class="pl-11 w-full px-4 py-2.5 bg-gray-100/50 border border-border-main rounded-xl focus:ring-2 focus:ring-primary/10 focus:border-primary outline-none transition-all text-sm font-bold" placeholder="Cari nama / NIS...">
                </div>

                <label class="inline-flex items-center gap-2 text-xs font-black text-text-muted uppercase tracking-widest">
                    <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-border-main text-primary focus:ring-primary/20">
                    Pilih Semua (Hasil Filter)
                </label>
            </div>

            <x-table>
                <x-table.thead>
                    <x-table.tr>
                        <x-table.th>Nama</x-table.th>
                        <x-table.th>NIS</x-table.th>
                        <x-table.th class="w-12 text-right">#</x-table.th>
                    </x-table.tr>
                </x-table.thead>
                <tbody class="divide-y divide-border-subtle">
                    @forelse($students as $student)
                    <x-table.tr>
                        <x-table.td class="text-sm font-black text-text-main uppercase">{{ $student->user->name }}</x-table.td>
                        <x-table.td class="text-xs font-bold text-text-muted uppercase tracking-widest">{{ $student->nis }}</x-table.td>
                        <x-table.td class="text-right">
                            <input type="checkbox" wire:model.live="selectedStudents" value="{{ $student->id }}" class="w-5 h-5 rounded border-border-main text-primary focus:ring-primary/20">
                        </x-table.td>
                    </x-table.tr>
                    @empty
                    <x-empty-state
                        colspan="3"
                        title="Semua Siswa Sudah Ter-assign"
                        message="Tidak ada siswa tanpa kelas saat ini."
                        icon="folder-open"
                    />
                    @endforelse
                </tbody>
            </x-table>

            @if($students->hasPages())
            <div>
                {{ $students->links() }}
            </div>
            @endif

            <div class="flex justify-end gap-3 pt-2">
                <x-button href="{{ route('admin.classes') }}" variant="secondary" class="font-black text-[10px] uppercase tracking-widest">
                    Batal
                </x-button>
                <x-button wire:click="assignSelected" variant="primary" class="font-black text-[10px] uppercase tracking-widest px-8">
                    Assign Ke {{ $classroom->name }}
                </x-button>
            </div>
        </div>
    </x-card>
</div>
