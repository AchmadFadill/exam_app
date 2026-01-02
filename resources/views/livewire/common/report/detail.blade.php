@section('title', 'Laporan Hasil Ujian')

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route($backRoute) }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-text-main">Detail Laporan</h2>
                <p class="text-text-muted text-sm">{{ $exam['exam_name'] }} • {{ $exam['class'] }}</p>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-card variant="stat" title="Rata-rata Nilai" :value="$exam['avg_score']" color="primary" />
        <x-card variant="stat" title="Nilai Tertinggi" :value="$exam['highest']" color="green" />
        <x-card variant="stat" title="Nilai Terendah" :value="$exam['lowest']" color="amber" />
        <x-card variant="stat" title="Total Peserta" :value="$exam['participants']" color="indigo" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Student List -->
        <div class="lg:col-span-2">
            <x-card title="Hasil Per Siswa">
                <div class="overflow-x-auto -mx-6 -my-6">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-500 font-medium">
                            <tr>
                                <th class="px-6 py-4">Nama Siswa</th>
                                <th class="px-6 py-4 text-center">Nilai</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($students as $student)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $student['name'] }}</div>
                                    <div class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5">{{ $student['started_at'] }} - {{ $student['submitted_at'] }}</div>
                                </td>
                                <td class="px-6 py-4 text-center font-black text-lg text-gray-900">{{ $student['score'] }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                        {{ $student['status'] == 'Lulus' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $student['status'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button class="text-primary hover:underline font-bold text-xs uppercase tracking-widest">Detail</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Most Failed Questions (Teacher Only maybe, or shared) -->
        <div class="lg:col-span-1">
            <x-card title="Analisis Soal">
                <p class="text-xs text-text-muted mb-4 uppercase tracking-widest font-bold">Soal Paling Banyak Salah</p>
                <div class="space-y-4">
                    @foreach($most_failed_questions as $q)
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 group hover:border-red-100 transition-colors">
                        <div class="flex justify-between items-start mb-2">
                            <span class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center font-bold text-sm text-gray-900">#{{ $q['number'] }}</span>
                            <span class="text-xs font-black text-red-600 uppercase tracking-widest">{{ $q['failed_percentage'] }}% Gagal</span>
                        </div>
                        <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed mb-3">{{ $q['text'] }}</p>
                        <div class="pt-3 border-t border-gray-200/50 text-[10px] text-gray-500">
                             Jawaban Benar: <span class="font-bold text-green-600">{{ $q['correct_answer'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button class="w-full mt-6 py-3 bg-gray-900 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-black transition-all">Lihat Analisis Lengkap</button>
            </x-card>
        </div>
    </div>
</div>
