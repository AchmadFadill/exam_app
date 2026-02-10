<div x-data="{ 
    activeReason: null
}"
x-on:confirmed-approve-request.window="$wire.approve($event.detail)"
x-on:confirmed-reject-request.window="$wire.reject($event.detail)"
x-on:confirmed-bulk-approve.window="$wire.bulkApprove()"
x-on:confirmed-bulk-reject.window="$wire.bulkReject()">
    <x-header 
        title="Permintaan Reset Password" 
        subtitle="Kelola permintaan pemulihan akun dari pengguna." 
    />

    @if (session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="relative overflow-x-auto">
        <x-table>
            <x-table.thead>
                <x-table.tr>
                    <x-table.th class="w-10 text-center">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-border-main text-primary focus:ring-primary/20 bg-bg-surface">
                    </x-table.th>
                    <x-table.th>User / NIS-Email</x-table.th>
                    <x-table.th class="hidden md:table-cell">Waktu</x-table.th>
                    <x-table.th class="hidden lg:table-cell text-center">Alasan</x-table.th>
                    <x-table.th class="text-right">Aksi</x-table.th>
                </x-table.tr>
            </x-table.thead>
            <tbody class="bg-bg-surface dark:bg-bg-surface divide-y divide-border-subtle dark:divide-border-subtle">
                @forelse ($requests as $request)
                    <x-table.tr wire:key="{{ $request->id }}" class="{{ in_array($request->id, $selected) ? 'bg-primary/5' : '' }} group">
                        <x-table.td class="text-center">
                            <input type="checkbox" value="{{ $request->id }}" wire:model.live="selected" class="rounded border-border-main text-primary focus:ring-primary/20 bg-bg-surface">
                        </x-table.td>
                        <x-table.td>
                            <div class="flex flex-col min-w-0">
                                <div class="font-black text-text-main uppercase tracking-tight truncate leading-tight">{{ $request->user->name }}</div>
                                <div class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-0.5 truncate italic">
                                    {{ $request->user->student->nis ?? $request->user->email ?? '-' }}
                                </div>
                                <div class="md:hidden mt-1 text-[9px] font-bold text-text-muted/60 uppercase">
                                    {{ $request->created_at->format('d M, H:i') }} • {{ $request->user->role }}
                                </div>
                                <div class="lg:hidden mt-2">
                                    <button @click="activeReason = @js($request->reason)" class="text-primary hover:text-blue-700 text-[10px] font-black uppercase tracking-widest flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Lihat Alasan
                                    </button>
                                </div>
                            </div>
                        </x-table.td>
                        <x-table.td class="hidden md:table-cell">
                            <div class="font-black text-text-main uppercase tracking-tight text-xs">{{ $request->created_at->format('d M Y') }}</div>
                            <div class="text-[9px] text-text-muted font-bold mt-0.5 opacity-60">{{ $request->created_at->format('H:i') }} ({{ $request->created_at->diffForHumans() }})</div>
                        </x-table.td>
                        <x-table.td class="hidden lg:table-cell text-center">
                            <button @click="activeReason = @js($request->reason)" class="text-primary hover:text-blue-700 text-[10px] font-black uppercase tracking-[0.2em] underline decoration-2 underline-offset-4 decoration-primary/20">
                                Lihat
                            </button>
                        </x-table.td>
                        <x-table.td>
                            <div class="flex justify-end gap-1.5 sm:gap-3">
                                <button @click="$dispatch('show-confirm-modal', [{ 
                                        title: 'Setujui Permintaan?', 
                                        message: 'Password user akan direset menjadi default (NIS/Email).', 
                                        type: 'primary',
                                        confirmText: 'Ya, Setujui',
                                        onConfirm: 'approve-request',
                                        onConfirmDetail: {{ $request->id }}
                                    }])" 
                                    class="px-3 sm:px-4 py-1.5 sm:py-2 bg-green-500/10 text-green-600 dark:text-green-400 rounded-lg sm:rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-widest hover:bg-green-500/20 transition-all border border-green-500/20 whitespace-nowrap">
                                    Approve
                                </button>
                                <button @click="$dispatch('show-confirm-modal', [{ 
                                        title: 'Tolak Permintaan?', 
                                        message: 'Permintaan reset ini akan dibatalkan tanpa mengubah password user.', 
                                        type: 'danger',
                                        confirmText: 'Ya, Tolak',
                                        onConfirm: 'reject-request',
                                        onConfirmDetail: {{ $request->id }}
                                    }])" 
                                    class="px-3 sm:px-4 py-1.5 sm:py-2 bg-red-500/10 text-red-600 dark:text-red-400 rounded-lg sm:rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-widest hover:bg-red-500/20 transition-all border border-red-500/20 whitespace-nowrap">
                                    Reject
                                </button>
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @empty
                    <x-empty-state 
                        colspan="5" 
                        title="Semua Aman!" 
                        message="Tidak ada permintaan reset password yang tertunda saat ini." 
                        icon="folder-open" 
                    />
                @endforelse
            </tbody>
        </x-table>

        <!-- Bulk Actions Floating Bar -->
        <div x-show="$wire.selected.length > 0" 
             x-transition:enter="transition ease-out duration-300 transform translate-y-20 opacity-0"
             x-transition:enter-start="translate-y-20 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-20 opacity-0"
             class="fixed bottom-6 sm:bottom-10 left-1/2 transform -translate-x-1/2 bg-slate-900/95 backdrop-blur-md text-white px-4 sm:px-8 py-3 sm:py-5 rounded-2xl sm:rounded-[2rem] shadow-2xl z-50 flex items-center gap-4 sm:gap-8 border border-white/10 w-[calc(100%-2rem)] sm:w-auto overflow-x-auto"
             style="display: none;">
            
            <div class="flex flex-col shrink-0">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-white/40 leading-none">Terpilih</span>
                <span class="text-sm sm:text-lg font-black tracking-tight mt-1 leading-none"><span x-text="$wire.selected.length"></span> Item</span>
            </div>
            
            <div class="h-8 sm:h-10 w-px bg-white/10 shrink-0"></div>

            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                <button @click="$dispatch('show-confirm-modal', [{ 
                        title: 'Approve Semua?', 
                        message: 'Setujui semua permintaan reset password yang dipilih.', 
                        type: 'primary',
                        confirmText: 'Ya, Setujui Semua',
                        onConfirm: 'bulk-approve'
                    }])" class="px-4 sm:px-6 py-2 sm:py-3 bg-white text-slate-900 rounded-xl sm:rounded-2xl text-[9px] sm:text-[10px] font-black uppercase tracking-widest hover:bg-blue-50 transition-all transform active:scale-95 whitespace-nowrap">
                    Approve <span class="hidden xs:inline">All</span>
                </button>
                
                <button @click="$dispatch('show-confirm-modal', [{ 
                        title: 'Tolak Semua?', 
                        message: 'Tolak semua permintaan reset password yang dipilih.', 
                        type: 'danger',
                        confirmText: 'Ya, Tolak Semua',
                        onConfirm: 'bulk-reject'
                    }])" class="px-4 sm:px-6 py-2 sm:py-3 bg-red-500 text-white rounded-xl sm:rounded-2xl text-[9px] sm:text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-all transform active:scale-95 shadow-lg shadow-red-500/20 whitespace-nowrap">
                    Reject <span class="hidden xs:inline">All</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Reason Modal -->
    <div x-show="activeReason !== null" 
         x-cloak 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div x-show="activeReason !== null" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="activeReason = null" 
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-md"></div>

        <!-- Modal Content -->
        <div x-show="activeReason !== null" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative bg-bg-surface dark:bg-slate-900 rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden border border-white/10">
            
            <div class="p-10">
                <div class="flex items-center gap-5 mb-8">
                    <div class="w-16 h-16 bg-primary/10 rounded-[1.5rem] flex items-center justify-center text-primary">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-text-main tracking-tight uppercase italic leading-none">Alasan Reset</h3>
                        <p class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-2">Pesan Dari Pengguna</p>
                    </div>
                </div>

                <div class="p-8 bg-gray-50/50 dark:bg-slate-800/50 rounded-[2rem] border border-border-main dark:border-slate-800 shadow-inner">
                    <p class="text-text-main font-bold text-sm leading-relaxed italic opacity-80" x-text="activeReason"></p>
                </div>
            </div>

            <div class="p-10 bg-gray-50/50 dark:bg-slate-800/30 border-t border-border-subtle dark:border-border-subtle">
                <button @click="activeReason = null" 
                        class="w-full px-8 py-4 rounded-2xl bg-primary hover:bg-blue-700 text-white font-black text-[10px] uppercase tracking-[0.2em] shadow-lg shadow-blue-200 transition-all transform active:scale-95">
                    Tutup Pesan
                </button>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $requests->links() }}
    </div>
</div>
