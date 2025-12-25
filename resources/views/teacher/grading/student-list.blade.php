@section('title', 'Daftar Siswa - ' . $examName)

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('teacher.grading.index') }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h2 class="font-bold text-2xl text-text-main">{{ $examName }}</h2>
            <p class="text-text-muted text-sm">{{ $className }}</p>
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
