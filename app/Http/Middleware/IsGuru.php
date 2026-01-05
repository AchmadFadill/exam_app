<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsGuru
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isTeacher()) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Guru.');
        }

        return $next($request);
    }
}
