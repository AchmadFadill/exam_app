@props(['title' => null])

@php
    $routeTitles = [
        // Admin Routes
        'admin.dashboard' => 'Beranda Admin',
        'admin.teachers' => 'Kelola Guru',
        'admin.students' => 'Kelola Siswa',
        'admin.classes' => 'Kelola Kelas',
        'admin.subjects' => 'Kelola Pelajaran',
        'admin.exams' => 'Kelola Ujian',
        'admin.exams.edit' => 'Edit Ujian',
        'admin.monitor' => 'Pantau Ujian',
        'admin.monitor.detail' => 'Detail Pantauan',
        'admin.grading.show' => 'Daftar Nilai Siswa',
        'admin.grading.detail' => 'Detail Jawaban Siswa',
        'admin.reports.index' => 'Laporan Nilai',
        'admin.reports.detail' => 'Detail Laporan Ujian',
        'admin.reports.student' => 'Hasil Ujian Siswa',
        'admin.reports.analysis' => 'Analisis Soal',
        'admin.settings' => 'Pengaturan Sistem',
        'admin.password-requests' => 'Permintaan Reset Password',

        // Teacher Routes
        'teacher.dashboard' => 'Beranda Guru',
        'teacher.questions' => 'Bank Soal',
        'teacher.questions.group' => 'Detail Grup Soal',
        'teacher.exams.index' => 'Daftar Ujian',
        'teacher.exams.create' => 'Buat Ujian Baru',
        'teacher.exams.edit' => 'Edit Ujian',
        'teacher.monitoring' => 'Pantau Ujian',
        'teacher.monitoring.detail' => 'Detail Pantauan',
        'teacher.grading.index' => 'Beri Nilai Ujian',
        'teacher.grading.show' => 'Daftar Jawaban Siswa',
        'teacher.grading.detail' => 'Koreksi Jawaban',
        'teacher.reports.index' => 'Laporan Nilai',
        'teacher.reports.detail' => 'Detail Laporan',
        'teacher.reports.student' => 'Hasil Ujian Siswa',
        'teacher.reports.analysis' => 'Analisis Soal',
        'teacher.settings' => 'Pengaturan Profil',

        // Student Routes
        'student.dashboard' => 'Beranda Siswa',
        'student.exams.index' => 'Daftar Ujian',
        'student.exam.start' => 'Konfirmasi Ujian',
        'student.exam.show' => 'Sedang Ujian',
        'student.results' => 'Riwayat Hasil Ujian',
        'student.results.detail' => 'Detail Hasil Ujian',
        'student.settings' => 'Pengaturan Profil',
        
        // Auth Routes
        'login' => 'Login Admin',
        'teacher.login' => 'Login Guru',
        'student.login' => 'Login Siswa',
        'student.password-reset' => 'Reset Password Siswa',
        'teacher.password-reset' => 'Reset Password Guru',
    ];

    $resolvedTitle = $title;
    
    // If title is a generic role name or empty, resolve from route
    $genericTitles = ['Administrator', 'Guru', 'Siswa', 'Dashboard', 'Beranda', 'User', ''];
    if (!$resolvedTitle || in_array($resolvedTitle, $genericTitles)) {
        $currentRoute = request()->route()?->getName();
        
        if ($currentRoute && isset($routeTitles[$currentRoute])) {
            $resolvedTitle = $routeTitles[$currentRoute];
        } else {
            // Fallback: try matching with wildcard for resource routes
            foreach ($routeTitles as $route => $label) {
                if (request()->routeIs($route . '*')) {
                    $resolvedTitle = $label;
                    break;
                }
            }
        }
    }

    // Final fallback if still generic or null
    $resolvedTitle = $resolvedTitle ?: 'Dashboard';
@endphp

{{ $resolvedTitle }}
