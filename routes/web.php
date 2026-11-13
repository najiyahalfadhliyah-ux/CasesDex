<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\UserController;
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

    // Kasus: hak akses admin/petugas diatur CaseRecordPolicy, bukan middleware role
    Route::resource('cases', CaseController::class);
    Route::patch('/cases/{case}/status', [CaseController::class, 'updateStatus'])->name('cases.status');

    // Barang bukti: tambah di bawah kasus, sisanya langsung /evidence/{id}. Hak akses lewat EvidencePolicy.
    Route::resource('cases.evidence', EvidenceController::class)->shallow()->only(['create', 'store', 'show', 'edit', 'update']);
    Route::get('/evidence/{evidence}/download', [EvidenceController::class, 'download'])->name('evidence.download');
    Route::post('/evidence/{evidence}/hash-check', [EvidenceController::class, 'checkHash'])->name('evidence.hash-check');
    Route::patch('/evidence/{evidence}/toggle-active', [EvidenceController::class, 'toggleActive'])->name('evidence.toggle-active');

    // Pengaturan akun sendiri (semua peran)
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');

    // Khusus Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin-test', fn () => 'Halaman khusus admin')->name('admin.test');

        // Manajemen pengguna: tanpa destroy, akun dinonaktifkan bukan dihapus
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        // audit log, dst.
    });
});