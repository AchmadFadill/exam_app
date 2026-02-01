<x-card {{ $attributes->merge(['class' => 'overflow-hidden']) }}>
    <div class="overflow-x-auto -mx-6 -my-6">
        <table class="w-full text-left border-collapse">
            {{ $slot }}
        </table>
    </div>

    @if(isset($footer))
        <x-slot name="footer">{{ $footer }}</x-slot>
    @endif

    @if(isset($after))
        {{ $after }}
    @endif
</x-card>
