<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSiswa
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('student.login');
        }

        // Allow access for both students and admins
        if (!auth()->user()->isStudent() && !auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Siswa.');
        }

        return $next($request);
    }
}
