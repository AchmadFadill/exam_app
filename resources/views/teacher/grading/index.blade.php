@section('title', 'Penilaian Essay')

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-bold text-2xl text-text-main">Penilaian Essay</h2>
            <p class="text-text-muted text-sm">Daftar ujian yang memerlukan koreksi manual</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Nama Ujian</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Kelas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Status</th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-bg-surface divide-y divide-gray-200">
                    @foreach($exams as $exam)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-text-main">{{ $exam['name'] }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                            {{ $exam['class'] }}
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                            {{ $exam['date'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap space-y-1">
                            @if($exam['pending_count'] > 0)
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800">
                                    Butuh Koreksi ({{ $exam['pending_count'] }})
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Selesai Dinilai
                                </span>
                            @endif
                            <br>
                            @if($exam['is_published'] ?? false)
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 text-[10px] uppercase tracking-wider">
                                    Sudah Terbit (Published)
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-800 text-[10px] uppercase tracking-wider">
                                    Belum Terbit (Draft)
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            @if(!($exam['is_published'] ?? false) && $exam['pending_count'] == 0)
                                <x-button variant="soft" class="bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 border-blue-200">
                                    Publish Nilai
                                </x-button>
                            @endif

                            @if($exam['pending_count'] > 0)
                                <x-button href="{{ route('teacher.grading.show', ['exam' => $exam['id']]) }}" variant="soft">Koreksi</x-button>
                            @else
                                <x-button href="{{ route('teacher.grading.show', ['exam' => $exam['id']]) }}" variant="secondary" class="border-transparent bg-transparent hover:bg-transparent hover:text-primary shadow-none p-0">Lihat Detail</x-button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

