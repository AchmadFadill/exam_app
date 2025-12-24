@props(['classes' => []])

<div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
        <!-- Search -->
        <div class="w-full md:w-1/3 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
            <input 
                type="text" 
                placeholder="Cari nama siswa..." 
                class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-all duration-200"
            >
        </div>

        <!-- Filters -->
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <!-- Status Filter -->
            <div class="relative min-w-[160px]">
                <select class="appearance-none w-full bg-gray-50 border border-gray-200 text-gray-700 py-2.5 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 text-sm transition-all duration-200">
                    <option value="">Status Pengerjaan</option>
                    <option value="working">Sedang Mengerjakan</option>
                    <option value="completed">Selesai</option>
                    <option value="not_started">Belum Mulai</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                </div>
            </div>

            <!-- Class Filter -->
            <div class="relative min-w-[160px]">
                <select class="appearance-none w-full bg-gray-50 border border-gray-200 text-gray-700 py-2.5 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 text-sm transition-all duration-200">
                    <option value="">Kelas</option>
                    {{-- Dummy Data --}}
                    <option value="XI IPA 1">XI IPA 1</option>
                    <option value="XI IPA 2">XI IPA 2</option>
                    <option value="XI IPS 1">XI IPS 1</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                </div>
            </div>
        </div>
    </div>
</div>
