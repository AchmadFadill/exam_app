<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceAdminPasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if (!$user->isAdmin() || !$user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('admin.password.force', 'admin.password.force.update')) {
            return $next($request);
        }

        return redirect()->route('admin.password.force');
    }
}
