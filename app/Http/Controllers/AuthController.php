<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request, AuditLogger $audit): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Batas percobaan: 5 kali salah per login + IP, lalu kunci 5 menit
        $throttleKey = Str::transliterate(Str::lower($credentials['login'])).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $wait = $seconds >= 60 ? ceil($seconds / 60).' menit' : $seconds.' detik';

            return back()
                ->withErrors(['login' => "Terlalu banyak percobaan, coba lagi dalam {$wait}."])
                ->onlyInput('login');
        }

        // FR-002: boleh pakai email atau username
        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $attempt = [
            $field => $credentials['login'],
            'password' => $credentials['password'],
            'is_active' => true, // akun nonaktif tidak bisa masuk
        ];

        if (Auth::attempt($attempt)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            $audit->log('auth.login', $request->user(), 'Masuk dari IP '.$request->ip());

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 300); // 300 detik = 5 menit

        // Hanya nama login yang dicatat, kata sandi tidak pernah. Pelaku = Sistem (belum ada pengguna login).
        $who = 'Login "'.Str::limit($credentials['login'], 100).'" dari IP '.$request->ip();
        $audit->log('auth.login_failed', null, $who);
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $audit->log('auth.locked', null, $who.', dikunci 5 menit'); // tercatat sekali, saat percobaan ke-5
        }

        // Pesan sengaja umum: tidak memberi tahu apakah username, sandi, atau status akun yang salah
        return back()
            ->withErrors(['login' => 'Email/username atau kata sandi salah.'])
            ->onlyInput('login');
    }

    public function logout(Request $request, AuditLogger $audit): RedirectResponse
    {
        $audit->log('auth.logout', $request->user()); // dicatat sebelum sesi dihapus

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}