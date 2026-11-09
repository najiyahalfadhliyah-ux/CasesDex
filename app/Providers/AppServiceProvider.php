<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Link halaman ($items->links()) memakai gaya Bootstrap, bukan Tailwind bawaan
        Paginator::useBootstrapFive();

        // Aturan kata sandi bersama (manajemen pengguna + pengaturan akun), dipakai lewat Password::defaults()
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());
    }
}
