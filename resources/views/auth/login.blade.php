@extends('layouts.guest')

@section('title', 'Masuk - CasesDex')

@php
    // Keadaan halaman dibaca dari pesan error AuthController (controller tidak mengirim flag terpisah).
    // shortcut: cocok teks pesan; kalau kalimat di AuthController diubah, ubah juga di sini.
    $loginError = $errors->first('login');
    $locked = str_starts_with($loginError, 'Terlalu banyak percobaan');
    $wrongCredentials = $loginError === 'Email/username atau kata sandi salah.';
    $passwordInvalid = $errors->has('password') || $wrongCredentials;
@endphp

@section('content')
<div class="auth-layout">
    {{-- Panel kiri: identitas. Disembunyikan di bawah 992px agar form tampil penuh. --}}
    <aside class="auth-panel d-none d-lg-flex">
        <p class="auth-kicker">CasesDex / Arsip kasus digital</p>

        <div>
            {{-- Panel selalu gelap, jadi selalu pakai logo versi gelap --}}
            <img src="{{ asset('images/logo-simbol-gelap.png') }}" alt="" height="150" class="auth-symbol">
            <h2 class="auth-title">Setiap kasus terstruktur. Setiap bukti terjaga.</h2>
            <p class="auth-tagline">Satu ruang kerja untuk dokumentasi kasus, barang bukti, dan jejak aktivitas.</p>
            <ul class="auth-chips">
                <li>SHA-256</li>
                <li>Akses berbasis peran</li>
                <li>Audit aktivitas</li>
            </ul>
        </div>

        <small>Internal · Seluruh data demonstrasi bersifat fiktif</small>
    </aside>

    {{-- Panel kanan: form --}}
    <main class="auth-main" id="konten">
        <div class="auth-form">
            @if (Route::has('home'))
                <a href="{{ route('home') }}" class="auth-back">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> Beranda
                </a>
            @endif

            <x-logo type="teks" height="150" alt="CasesDex" class="auth-logo" />

            <h1 class="auth-heading">Selamat datang kembali</h1>
            <p class="text-muted mb-4">Masuk untuk mengakses ruang kerja CasesDex.</p>

            @if ($errors->any())
                <div id="login-alert" @class(['auth-alert', 'auth-alert-locked' => $locked]) role="alert">
                    <i class="bi {{ $locked ? 'bi-lock' : 'bi-exclamation-circle' }}" aria-hidden="true"></i>
                    <div>
                        <p class="auth-alert-title">{{ $locked ? 'Akun dikunci sementara' : 'Gagal masuk' }}</p>
                        @foreach ($errors->all() as $message)
                            <p class="mb-0">{{ $message }}</p>
                        @endforeach
                        @if ($locked)
                            <p class="mb-0 mt-1">Percobaan masuk dibatasi untuk melindungi akun. Hubungi admin bila Anda lupa kata sandi.</p>
                        @endif
                    </div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="login" class="form-label">Email atau username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                        <input id="login" name="login" type="text" value="{{ old('login') }}" autocomplete="username"
                               @class(['form-control', 'is-invalid' => $errors->has('login')])
                               required autofocus
                               @if ($errors->has('login')) aria-invalid="true" aria-describedby="login-alert" @endif>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Kata sandi</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
                        <input id="password" name="password" type="password" autocomplete="current-password"
                               @class(['form-control', 'is-invalid' => $passwordInvalid])
                               required
                               @if ($passwordInvalid) aria-invalid="true" aria-describedby="login-alert" @endif>
                        <button type="button" class="btn btn-secondary" id="togglePassword"
                                aria-label="Tampilkan kata sandi" aria-pressed="false">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Masuk ke dashboard <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </button>
            </form>

            <p class="auth-note">Akun hanya dibuat oleh Admin. Tidak ada pendaftaran mandiri.</p>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    // Tombol tampilkan/sembunyikan kata sandi
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        this.setAttribute('aria-pressed', show);
        this.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        this.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
</script>
@endpush
