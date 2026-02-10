@props([
    'title',
    'subtitle' => null
])

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="font-black text-xl sm:text-2xl text-text-main uppercase tracking-tight">{{ $title }}</h2>
        @if($subtitle)
            <p class="text-text-muted text-sm mt-1 font-medium">{{ $subtitle }}</p>
        @endif
    </div>
    @if($slot->isNotEmpty())
        <div class="flex items-center gap-3">
            {{ $slot }}
        </div>
    @endif
</div>
