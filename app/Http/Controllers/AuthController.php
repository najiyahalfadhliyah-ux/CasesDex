<?php

namespace App\Http\Controllers;

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

    public function login(Request $request): RedirectResponse
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

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 300); // 300 detik = 5 menit

        // Pesan sengaja umum: tidak memberi tahu apakah username, sandi, atau status akun yang salah
        return back()
            ->withErrors(['login' => 'Email/username atau kata sandi salah.'])
            ->onlyInput('login');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}