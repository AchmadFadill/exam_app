<div class="relative" x-data="{ openNotifications: false }">
    <button 
        @click="openNotifications = !openNotifications; $dispatch('close-profile')"
        class="relative p-2 text-text-muted hover:text-primary active:scale-95 transition-all duration-200 rounded-xl hover:bg-primary/10"
    >
        @if($this->notifications->count() > 0)
            <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-bg-surface animate-pulse"></span>
        @endif
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
    </button>

    <!-- Notification Dropdown -->
    <div 
        x-show="openNotifications"
        @click.away="openNotifications = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        class="absolute right-0 mt-3 w-80 bg-bg-surface rounded-2xl shadow-2xl border border-border-main py-2 overflow-hidden z-50"
        style="display: none;"
    >
        <div class="px-4 py-2 border-b border-border-subtle bg-bg-app/50 flex justify-between items-center">
            <h3 class="font-bold text-sm text-text-main">Notifikasi</h3>
            @if($this->notifications->count() > 0)
                <button wire:click="markAllAsRead" class="text-[10px] text-primary hover:underline">Tandai Semua Dibaca</button>
            @endif
        </div>
        
        <div class="max-h-64 overflow-y-auto">
            @forelse($this->notifications as $notification)
                <div 
                    wire:click="markAsRead('{{ $notification->id }}')" 
                    class="block px-4 py-3 hover:bg-primary/5 transition-colors border-b border-border-subtle cursor-pointer relative group"
                >
                    <div class="flex justify-between items-start">
                        <p class="text-xs font-semibold text-text-main">{{ $notification->data['title'] ?? 'Notifikasi' }}</p>
                        <span class="h-1.5 w-1.5 rounded-full bg-primary mt-1"></span>
                    </div>
                    <p class="text-[10px] text-text-muted mt-0.5">{{ $notification->data['message'] ?? '' }}</p>
                    <span class="text-[9px] text-primary font-medium mt-1 inline-block">{{ $notification->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-text-muted">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-xs">Tidak ada notifikasi baru</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
