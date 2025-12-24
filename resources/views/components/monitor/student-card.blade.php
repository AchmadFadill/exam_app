@props(['student'])

<div class="bg-white rounded-lg border {{ $student['tab_alert'] >= 3 ? 'border-red-300 ring-2 ring-red-100' : 'border-gray-200' }} p-4 shadow-sm relative overflow-hidden">
    @if($student['status'] == 'completed')
        <div class="absolute top-0 right-0 p-1.5 bg-green-500 rounded-bl-lg">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
    @endif

    <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-text-muted font-bold text-sm">
            {{ substr($student['name'], 0, 2) }}
        </div>
        <div>
             <h4 class="font-semibold text-text-main text-sm truncate w-32 md:w-40">{{ $student['name'] }}</h4>
             <p class="text-xs text-text-muted mb-0.5">{{ $student['class'] ?? 'XI IPA 1' }}</p>
             <p class="text-xs {{ $student['status'] == 'working' ? 'text-primary' : 'text-text-muted' }}">
                 @if($student['status'] == 'working') Sedang Mengerjakan
                 @elseif($student['status'] == 'completed') Selesai
                 @else Belum Mulai @endif
             </p>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="w-full bg-gray-100 rounded-full h-2.5 mb-2">
        <div class="bg-primary h-2.5 rounded-full transition-all duration-500" style="width: {{ $student['w'] }}"></div>
    </div>
    <div class="flex justify-between text-xs text-text-muted mb-3">
        <span>Soal Terjawab: {{ $student['progress'] }}</span>
        <span>{{ $student['w'] }}</span>
    </div>

    <!-- Alerts -->
    @if($student['tab_alert'] > 0)
    <div class="bg-amber-50 rounded px-2 py-1 flex items-center gap-2 text-xs text-amber-700 mb-3">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        Pindah Tab: <strong>{{ $student['tab_alert'] }}x</strong>
    </div>
    @endif

    <!-- Actions Slot -->
    <div class="grid grid-cols-2 gap-2 mt-2">
        {{ $slot }}
    </div>
</div>
