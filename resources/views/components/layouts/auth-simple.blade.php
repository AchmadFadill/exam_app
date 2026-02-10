<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Login' }} - CBT Exam</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}?v=3">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full min-h-screen flex items-center justify-center p-4 antialiased selection:bg-blue-100 selection:text-blue-900 font-sans bg-bg-app text-text-main">
    <div class="max-w-md w-full relative my-auto">
        <!-- Subtle branding accent -->
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-[var(--color-primary)]/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-[var(--color-secondary)]/5 rounded-full blur-3xl"></div>

        <div class="relative bg-[var(--color-bg-surface)] p-8 sm:p-10 rounded-[2rem] shadow-[0_20px_60px_-15px_rgba(30,64,175,0.08)] border border-white/60">
            <div class="text-center">
                <!-- Circular School Logo -->
                <div class="relative inline-block mb-6 group">
                    <div class="absolute inset-0 bg-[var(--color-primary)]/10 blur-xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative w-20 h-20 bg-white rounded-full flex items-center justify-center p-2 shadow-xl border border-gray-50 transition-transform duration-500 group-hover:scale-105">
                        @if(isset($app_logo) && $app_logo)
                            <img src="{{ asset('storage/' . $app_logo) }}" alt="Logo Sekolah" class="w-full h-full object-cover">
                        @else
                            <img src="{{ asset('img/logo_school.jpg') }}" alt="Logo Sekolah" class="w-full h-full object-cover">
                        @endif
                    </div>
                </div>

                <h1 class="text-2xl font-extrabold text-[var(--color-text-main)] tracking-tight">{{ $title }}</h1>
                <p class="mt-1 text-xs text-[var(--color-text-muted)] font-medium">CBT {{ $app_name ?? 'Sistem' }}</p>
            </div>
            
            @if($action)
            <form class="mt-6 space-y-4" action="{{ $action }}" method="POST">
                @csrf
            @else
            <div class="mt-6 space-y-4">
            @endif

                {{-- Global Error Message --}}
                @if ($errors->any())
                <div class="p-3 rounded-2xl bg-red-50 border border-red-100 mb-3">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-[13px] font-semibold text-red-700">{{ $errors->first() }}</p>
                    </div>
                </div>
                @endif

                {{ $slot }}

            @if($action)
            </form>
            @else
            </div>
            @endif

            <!-- Alternative Login Links -->
            @if(isset($footer))
            <div class="mt-5 pt-4 border-t border-gray-100">
                <p class="text-[10px] text-center text-[var(--color-text-muted)] font-bold mb-2.5 uppercase tracking-wider">Login Sebagai</p>
                <div class="flex gap-2">
                    {{ $footer }}
                </div>
            </div>
            @endif
            
            <!-- Bottom Branding -->
            <div class="mt-6 text-center border-t border-gray-100 pt-5">
                <p class="text-[9px] text-slate-400 font-bold tracking-[0.25em] uppercase opacity-70">
                    &copy; {{ date('Y') }} CBT {{ $app_name ?? 'Sistem' }}
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const openPaths = icon.querySelectorAll('.eye-open');
            const closedPath = icon.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                openPaths.forEach(p => p.classList.add('hidden'));
                closedPath.classList.remove('hidden');
            } else {
                input.type = 'password';
                openPaths.forEach(p => p.classList.remove('hidden'));
                closedPath.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
