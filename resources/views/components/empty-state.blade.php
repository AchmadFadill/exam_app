@props([
    'title' => 'Tidak Ada Data',
    'message' => 'Belum ada informasi yang dapat ditampilkan saat ini.',
    'icon' => 'folder-open',
    'colspan' => null,
])

@php
    $icons = [
        'folder-open' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />',
        'bell-off' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />',
        'document-text' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
        'academic-cap' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />',
        'coffee' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 8g0 0 1 1-1h1a4 4 0 0 1 0 8h-1a1 1 0 0 1-1-1V8z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 1v3M10 1v3M14 1v3" />',
        'search' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />',
    ];

    $iconPath = $icons[$icon] ?? $icons['folder-open'];
@endphp

@if($colspan)
    <x-table.tr>
        <x-table.td :colspan="$colspan" class="py-24 text-center">
            <div class="flex flex-col items-center justify-center max-w-sm mx-auto animate-fadeIn">
                <div class="w-24 h-24 bg-primary/5 rounded-[2.5rem] flex items-center justify-center mb-8 relative group">
                    <div class="absolute inset-0 bg-primary/10 rounded-[2.5rem] scale-110 opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                    <svg class="w-10 h-10 text-primary opacity-40 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $iconPath !!}
                    </svg>
                </div>
                <h3 class="text-xl font-black text-text-main tracking-tight uppercase mb-3 opacity-90 italic">
                    {{ $title }}
                </h3>
                <p class="text-xs font-bold text-text-muted leading-relaxed uppercase tracking-widest opacity-60">
                    {!! $message !!}
                </p>
                
                @if($slot->isNotEmpty())
                    <div class="mt-8">
                        {{ $slot }}
                    </div>
                @endif
            </div>
        </x-table.td>
    </x-table.tr>
@else
    <div class="flex flex-col items-center justify-center h-full py-20 bg-bg-surface dark:bg-slate-900 border border-border-main dark:border-slate-800 rounded-[2.5rem] shadow-xl shadow-black/5 animate-fadeIn">
        <div class="w-24 h-24 bg-primary/5 rounded-[2.5rem] flex items-center justify-center mb-8 relative group">
            <div class="absolute inset-0 bg-primary/10 rounded-[2.5rem] scale-110 opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
            <svg class="w-10 h-10 text-primary opacity-40 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $iconPath !!}
            </svg>
        </div>
        <h3 class="text-xl font-black text-text-main tracking-tight uppercase mb-3 opacity-90 italic">
            {{ $title }}
        </h3>
        <p class="text-xs font-bold text-text-muted leading-relaxed uppercase tracking-widest opacity-60 text-center max-w-xs px-6">
            {!! $message !!}
        </p>

        @if($slot->isNotEmpty())
            <div class="mt-8">
                {{ $slot }}
            </div>
        @endif
    </div>
@endif
