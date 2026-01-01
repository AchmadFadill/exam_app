@section('title', $questionId ? 'Edit Soal' : 'Buat Soal Baru')

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('teacher.question-bank.index') }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h2 class="font-bold text-2xl text-text-main">
            {{ $questionId ? 'Edit Soal' : 'Buat Soal Baru' }}
        </h2>
    </div>

    <div class="bg-bg-surface rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <form wire:submit.prevent="save" class="p-6 space-y-6">
            
            <!-- Type & Subject Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-text-main mb-1">Mata Pelajaran</label>
                    <select wire:model="subject" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main">
                        <option value="">Pilih Mata Pelajaran</option>
                        <option value="Matematika">Matematika</option>
                        <option value="Biologi">Biologi</option>
                        <option value="Sejarah">Sejarah</option>
                        <option value="Geografi">Geografi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-main mb-1">Tipe Soal</label>
                    <select wire:model.live="type" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main">
                        <option value="multiple_choice">Pilihan Ganda</option>
                        <option value="essay">Essay</option>
                    </select>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6">
                <label class="block text-sm font-medium text-text-main mb-2">Pertanyaan</label>
                <!-- Simple WYSIWYG Placeholder or Textarea -->
                <textarea wire:model="question_text" rows="4" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main" placeholder="Tulis pertanyaan di sini..."></textarea>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-text-main mb-2">Gambar (Opsional)</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-4 text-text-muted" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.017 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                </svg>
                                <p class="mb-2 text-sm text-text-muted"><span class="font-semibold">Klik untuk upload</span> atau drag and drop</p>
                            </div>
                            <input id="dropzone-file" type="file" class="hidden" wire:model="question_image" />
                        </label>
                    </div>
                </div>
            </div>

            <!-- Conditional Fields based on Type -->
            @if($type === 'multiple_choice')
            <div class="border-t border-gray-100 pt-6 space-y-4">
                <label class="block text-sm font-medium text-text-main mb-2">Pilihan Jawaban</label>
                <div class="space-y-3">
                    @foreach($options as $index => $option)
                    <div class="flex items-start gap-3">
                        <div class="pt-2">
                             <input type="radio" name="correct_answer" wire:click="setCorrectAnswer({{ $index }})" {{ $correct_answer_index === $index ? 'checked' : '' }} class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary">
                        </div>
                        <div class="flex-1">
                            <div class="flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    {{ chr(65 + $index) }}
                                </span>
                                <input type="text" wire:model="options.{{ $index }}.text" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:ring-primary focus:border-primary sm:text-sm" placeholder="Pilihan {{ chr(65 + $index) }}">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div class="border-t border-gray-100 pt-6">
                <label class="block text-sm font-medium text-text-main mb-2">Pembahasan (Opsional)</label>
                <textarea wire:model="explanation" rows="3" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main" placeholder="Jelaskan kenapa jawaban tersebut benar..."></textarea>
            </div>

            @elseif($type === 'essay')
            <div class="border-t border-gray-100 pt-6">
                <label class="block text-sm font-medium text-text-main mb-2">Kunci Jawaban / Panduan Penilaian</label>
                <textarea wire:model="answer_key" rows="5" class="w-full border-gray-200 rounded-lg focus:ring-primary focus:border-primary text-text-main" placeholder="Tuliskan jawaban yang diharapkan atau poin-poin penting..."></textarea>
            </div>
            @endif



            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                <a href="{{ route('teacher.question-bank.index') }}" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-text-main hover:bg-gray-50 font-medium transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-blue-700 text-white rounded-lg font-medium transition-colors shadow-sm">Simpan Soal</button>
            </div>
        </form>
    </div>
</div>
</div>
