<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased select-none" oncontextmenu="return false;">
    
    <div class="min-h-screen flex flex-col">
        <!-- Minimalist Header -->
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-50 fixed w-full top-0">
            <div class="flex items-center">
                <!-- Logo / School Name -->
                <div class="flex-shrink-0 flex items-center">
                    <span class="text-xl font-bold text-blue-600">CBT SMAIT Baitul Muslim</span>
                </div>
                <div class="hidden md:ml-6 md:flex md:items-center border-l border-gray-200 pl-6">
                    <div>
                        <div class="text-sm font-medium text-gray-900">Ujian Akhir Semester</div>
                        <div class="text-xs text-gray-500">Matematika - Kelas X</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                {{ $header_actions ?? '' }}
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 mt-16 pb-20 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
