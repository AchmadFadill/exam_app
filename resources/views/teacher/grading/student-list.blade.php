@section('title', 'Daftar Siswa - ' . $examName)

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('teacher.grading.index') }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div class="flex-1">
            <h2 class="font-bold text-2xl text-text-main">{{ $examName }}</h2>
            <p class="text-text-muted text-sm">{{ $className }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if($isPublished)
                <span class="inline-flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-xl text-sm font-bold border border-green-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                    Sudah Dipublikasikan
                </span>
            @else
                <button wire:click="publish" class="inline-flex items-center gap-2 bg-primary hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-lg shadow-blue-200 active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    Terbitkan Nilai (Publish)
                </button>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Nama Siswa</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Waktu Submit</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Nilai</th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-bg-surface divide-y divide-gray-200">
                    @foreach($students as $student)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-text-main">{{ $student['name'] }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                            {{ $student['submitted_at'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($student['status'] == 'Sudah Dinilai')
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ $student['status'] }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800">
                                    {{ $student['status'] }}
                                </span>
                            @endif
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-text-main font-bold">
                            {{ $student['score'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('teacher.grading.detail', ['exam' => $examId, 'student' => $student['id']]) }}" class="text-primary hover:text-blue-800 font-bold bg-blue-50 px-3 py-1 rounded-lg">
                                {{ $student['status'] == 'Sudah Dinilai' ? 'Edit Nilai' : 'Beri Nilai' }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
