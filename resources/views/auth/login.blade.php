@extends('layouts.guest')

@section('title', 'Login - CasesDex')

@section('content')
<div class="row g-0 min-vh-100">
    {{-- Panel kiri hanya tampil di laptop --}}
    <aside class="col-lg-5 auth-panel d-none d-lg-flex flex-column justify-content-between p-5">
        <a href="{{ route('home') }}" class="text-white text-decoration-none fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-archive-fill" style="color:#FCD34D" aria-hidden="true"></i> Casesdex
        </a>
        <div>
            <h2 class="h3 fw-bold mb-3">Sistem Informasi CasesDex dan Dokumentasi Barang Bukti</h2>
            <p class="mb-0">Akses hanya untuk admin dan petugas yang terdaftar.</p>
        </div>
        <small>Memuat semua data Kasus yang kamu cari.</small>
    </aside>

    <main class="col-lg-7 d-flex align-items-center justify-content-center p-4">
        <div class="w-100 card-narrow">
            <a href="{{ route('home') }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small mb-4">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Beranda
            </a>
            <h1 class="h3 fw-bold mb-1">Masuk</h1>
            <p class="text-muted mb-4">Gunakan email atau username akun Anda.</p>

            <form action="{{ route('login') }}" method="POST" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="login" class="form-label">Email atau Username</label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                        <input id="login" name="login" type="text" value="{{ old('login') }}" autocomplete="username"
                               class="form-control @error('login') is-invalid @enderror" required autofocus
                               @error('login') aria-describedby="login-error" @enderror>
                        @error('login')
                            <div id="login-error" class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
                        <input id="password" name="password" type="password" autocomplete="current-password" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword"
                                aria-label="Tampilkan kata sandi" aria-pressed="false">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent w-100">Masuk</button>
            </form>
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
