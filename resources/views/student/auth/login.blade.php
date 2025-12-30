<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Siswa - CBT Exam</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
        <div class="text-center">
            <div class="mx-auto h-24 w-24 bg-white rounded-full flex items-center justify-center shadow-lg mb-6 overflow-hidden p-2">
                <img src="{{ asset('img/logo_school.jpg') }}" alt="Logo Sekolah" class="w-full h-full object-contain">
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Portal Siswa
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                CBT SMAIT Baitul Muslim
            </p>
        </div>
        
        <form class="mt-8 space-y-6" action="{{ route('student.dashboard') }}" method="GET">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="nis" class="sr-only">NIS / NISN</label>
                    <input id="nis" name="nis" type="text" required class="appearance-none rounded-none rounded-t-lg relative block w-full px-4 py-4 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Masukkan NIS / NISN">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required class="appearance-none rounded-none rounded-b-lg relative block w-full px-4 py-4 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Password">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                        Ingat Saya
                    </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500">
                        Lupa password?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-lg shadow-blue-200">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Masuk 
                </button>
            </div>
        </form>
        
        <div class="text-center text-xs text-gray-500 mt-4">
            &copy; 2025 CBT System. All rights reserved.
        </div>
    </div>
</body>
</html>
