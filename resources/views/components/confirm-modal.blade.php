<div x-data="{ 
    show: false,
    title: '',
    message: '',
    confirmText: 'Ya, Lanjutkan',
    cancelText: 'Batal',
    type: 'danger',
    onConfirm: null,
    onConfirmDetail: null,

    init() {
        window.addEventListener('show-confirm-modal', (event) => {
            const data = event.detail[0] || event.detail;
            this.title = data.title || 'Konfirmasi';
            this.message = data.message || 'Apakah Anda yakin?';
            this.confirmText = data.confirmText || 'Ya, Lanjutkan';
            this.cancelText = data.cancelText || 'Batal';
            this.type = data.type || 'danger';
            this.onConfirm = data.onConfirm;
            this.onConfirmDetail = data.onConfirmDetail || null;
            this.show = true;
        });
    },

    confirm() {
        if (this.onConfirm) {
            this.$dispatch('confirmed-' + this.onConfirm, this.onConfirmDetail);
        }
        this.show = false;
    }
}" 
x-show="show" 
x-cloak 
class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div x-show="show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="show = false" 
         class="fixed inset-0 bg-slate-950/60 backdrop-blur-md"></div>

    <!-- Modal Content -->
    <div x-show="show" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="relative bg-bg-surface dark:bg-slate-900 rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden border border-white/10">
        
        <div class="p-10 text-center">
            <!-- Icon based on type -->
            <div :class="{
                'bg-red-500/10 text-red-500': type === 'danger',
                'bg-amber-500/10 text-amber-500': type === 'warning',
                'bg-primary/10 text-primary': type === 'primary'
            }" class="w-20 h-20 rounded-[2rem] flex items-center justify-center mx-auto mb-8 transition-colors">
                <template x-if="type === 'danger'">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </template>
                <template x-if="type === 'warning'">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </template>
                <template x-if="type === 'primary'">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </template>
            </div>

            <h3 class="text-2xl font-black text-text-main tracking-tight uppercase mb-4" x-text="title"></h3>
            <p class="text-text-muted font-bold text-sm leading-relaxed" x-text="message"></p>
        </div>

        <div class="px-10 py-8 bg-gray-50/50 dark:bg-slate-800/30 border-t border-border-subtle dark:border-border-subtle flex flex-col sm:flex-row gap-4">
            <button @click="show = false" 
                    class="flex-1 px-8 py-4 rounded-2xl border border-border-main dark:border-slate-700 font-black text-[10px] uppercase tracking-[0.2em] text-text-muted hover:bg-gray-100 dark:hover:bg-slate-800 transition-all">
                <span x-text="cancelText"></span>
            </button>
            <button @click="confirm()" 
                    :class="{
                        'bg-red-500 hover:bg-red-600 shadow-red-200': type === 'danger',
                        'bg-amber-500 hover:bg-amber-600 shadow-amber-200': type === 'warning',
                        'bg-primary hover:bg-blue-700 shadow-blue-200': type === 'primary'
                    }"
                    class="flex-1 px-8 py-4 rounded-2xl text-white font-black text-[10px] uppercase tracking-[0.2em] shadow-lg transition-all transform active:scale-95">
                <span x-text="confirmText"></span>
            </button>
        </div>
    </div>
</div>
