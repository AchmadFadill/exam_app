<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show admin login form
     */
    public function showAdminLoginForm()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login');
    }

    /**
     * Show teacher login form
     */
    public function showTeacherLoginForm()
    {
        if (Auth::check() && Auth::user()->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        }
        return view('teacher.auth.login');
    }

    /**
     * Show student login form
     */
    public function showStudentLoginForm()
    {
        if (Auth::check() && Auth::user()->isStudent()) {
            return redirect()->route('student.dashboard');
        }
        return view('student.auth.login');
    }

    /**
     * Handle admin login
     */
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (!$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun ini bukan akun Admin. Silakan gunakan halaman login yang sesuai.',
                ])->withInput($request->only('email'));
            }

            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email'));
    }

    /**
     * Handle teacher login
     */
    public function teacherLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (!$user->isTeacher()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun ini bukan akun Guru. Silakan gunakan halaman login yang sesuai.',
                ])->withInput($request->only('email'));
            }

            $request->session()->regenerate();
            return redirect()->route('teacher.dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email'));
    }

    /**
     * Handle student login (NIS + Password)
     */
    public function studentLogin(Request $request)
    {
        $request->validate([
            'nis' => 'required|string',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        $student = \App\Models\Student::where('nis', $request->nis)->first();

        if (!$student) {
            return back()->withErrors([
                'nis' => 'NIS tidak ditemukan.',
            ])->withInput($request->only('nis'));
        }

        if (Auth::attempt(['email' => $student->user->email, 'password' => $request->password], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('student.dashboard'));
        }

        return back()->withErrors([
            'password' => 'Password salah.',
        ])->withInput($request->only('nis'));
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $role = Auth::user()?->role;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to appropriate login page
        return match ($role) {
            'admin' => redirect()->route('login'),
            'teacher' => redirect()->route('teacher.login'),
            'student' => redirect()->route('student.login'),
            default => redirect()->route('login'),
        };
    }
}
