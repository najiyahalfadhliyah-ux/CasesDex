<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Akun yang dinonaktifkan admin langsung keluar pada request berikutnya, apa pun driver sesinya
// (UserController::toggleActive hanya bisa menghapus sesi bila driver = database).
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['login' => 'Akun Anda telah dinonaktifkan. Hubungi admin.']);
        }

        return $next($request);
    }
}
