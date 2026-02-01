@props(['label' => null, 'options' => [], 'placeholder' => 'Pilih Opsi'])

<div {{ $attributes->except('wire:model')->merge(['class' => 'relative']) }}
    x-data="{
        open: false,
        selected: @entangle($attributes->wire('model')),
        options: @js($options),
        get label() {
            if (this.selected) {
                 // Check if options is array of objects {value, label}
                 let found = this.options.find(o => o.value == this.selected);
                 return found ? found.label : this.selected;
            }
            return '{{ $placeholder }}';
        },
        select(value) {
            this.selected = value;
            this.open = false;
        }
    }"
    @click.away="open = false"
>
    @if($label)
        <label class="block text-sm font-black text-text-main mb-2 uppercase tracking-widest opacity-70">{{ $label }}</label>
    @endif

    <div class="relative">
        <button type="button" @click="open = !open"
            class="w-full px-6 py-4 rounded-2xl border border-border-main dark:border-border-main bg-bg-surface dark:bg-slate-800/50 text-left flex justify-between items-center transition-all font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 group hover:border-primary/50"
            :class="open ? 'border-primary ring-4 ring-primary/10' : ''"
        >
            <span x-text="label" class="truncate pr-4" :class="!selected ? 'text-text-muted' : 'text-text-main dark:text-white'"></span>
            <div class="w-8 h-8 rounded-xl bg-gray-50 dark:bg-slate-700/50 flex items-center justify-center group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                 <svg class="w-4 h-4 text-text-muted transition-transform duration-300 group-hover:text-primary" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="absolute z-50 mt-2 w-full bg-white dark:bg-slate-800 rounded-2xl shadow-xl shadow-black/10 border border-border-main dark:border-border-main overflow-hidden max-h-60 overflow-y-auto transform origin-top"
            style="display: none;"
        >
            <ul class="py-2">
                <template x-for="option in options" :key="option.value">
                    <li @click="select(option.value)"
                        class="px-6 py-3 cursor-pointer hover:bg-primary/5 dark:hover:bg-white/5 transition-colors font-bold text-sm text-text-main dark:text-gray-200 flex items-center justify-between group border-l-4 border-transparent hover:border-primary"
                        :class="selected == option.value ? 'bg-primary/5 dark:bg-white/5 text-primary border-primary' : ''"
                    >
                        <span x-text="option.label"></span>
                        <svg x-show="selected == option.value" class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>
