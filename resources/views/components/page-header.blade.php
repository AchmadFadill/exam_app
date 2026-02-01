@props([
    'title',
    'highlight' => null,
    'subtitle' => null,
    'semester' => 'Genap 2025/2026',
    'date' => true
])

<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
    <div>
        <h1 class="text-4xl font-black text-text-main tracking-tight uppercase italic">
            {{ $title }} @if($highlight) <span class="text-primary not-italic">{{ $highlight }}</span> @endif
        </h1>
        @if($subtitle)
            <p class="text-text-muted mt-2 font-bold tracking-widest text-[10px] uppercase opacity-60">
                {{ $subtitle }}
            </p>
        @endif
    </div>
    
    <div class="flex items-center gap-4 shrink-0">
        @if($semester)
            <div class="px-5 py-2 bg-blue-50/50 dark:bg-primary/10 text-primary text-[10px] font-black rounded-2xl border border-primary/10 uppercase tracking-widest">
                {{ $semester }}
            </div>
        @endif
        
        @if($date)
            <div class="px-5 py-2 bg-bg-surface dark:bg-slate-800 border border-border-main dark:border-slate-700 rounded-2xl shadow-sm text-[10px] font-black text-text-muted uppercase tracking-widest">
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
