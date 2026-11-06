<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Landing page publik: tamu lihat halaman, yang sudah login langsung ke dashboard
Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : view('welcome'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // Khusus Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin-test', fn () => 'Halaman khusus admin')->name('admin.test');
        // manajemen pengguna, audit log, dst.
    });
});