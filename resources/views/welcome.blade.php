@extends('layouts.guest')

@section('content')
<header class="hero">
    <nav class="container d-flex align-items-center justify-content-between py-3">
        <span class="fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-archive-fill" style="color:#FCD34D" aria-hidden="true"></i> Arsip Kasus
        </span>
        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm px-3">Masuk</a>
    </nav>

    <div class="container py-5">
        <div class="row align-items-center g-5 py-lg-4">
            <div class="col-lg-7">
                <h1 class="mb-4">Arsip kasus dan barang bukti dalam satu tempat</h1>
                <p class="lead mb-4">Catat kasus, dokumentasikan bukti fisik dan digital, lalu pastikan berkasnya tidak berubah dengan cek SHA-256. Setiap perubahan tercatat.</p>
                <a href="{{ route('login') }}" class="btn btn-accent btn-lg px-4">
                    <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Masuk ke sistem
                </a>
            </div>

            {{-- Ilustrasi saja: nilai fiktif, bukan data dari database --}}
            <div class="col-lg-5" aria-hidden="true">
                <div class="evidence-tag">
                    <div class="tag-title">Barang Bukti</div>
                    <dl>
                        <dt>No. kasus</dt><dd>AK-2026-XXXX</dd>
                        <dt>Jenis</dt><dd>Digital (.pcap)</dd>
                        <dt>SHA-256</dt><dd>9f2c…e41a07b3</dd>
                        <dt>Diterima oleh</dt><dd>Petugas</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</header>

<main class="container py-5">
    <h2 class="h3 fw-bold mb-4">Yang bisa dilakukan</h2>
    <div class="row g-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 p-4">
                <div class="feature-icon mb-3"><i class="bi bi-folder2-open" aria-hidden="true"></i></div>
                <h3 class="h6 fw-semibold">Arsip kasus</h3>
                <p class="text-muted mb-0">Nomor kasus dibuat otomatis, status penanganan selalu terlihat.</p>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 p-4">
                <div class="feature-icon mb-3"><i class="bi bi-box-seam" aria-hidden="true"></i></div>
                <h3 class="h6 fw-semibold">Barang bukti</h3>
                <p class="text-muted mb-0">Foto, lokasi simpan, dan berkas bukti tersimpan per kasus.</p>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 p-4">
                <div class="feature-icon mb-3"><i class="bi bi-shield-check" aria-hidden="true"></i></div>
                <h3 class="h6 fw-semibold">Cek integritas</h3>
                <p class="text-muted mb-0">Hitung ulang SHA-256 untuk memastikan berkas bukti tidak berubah.</p>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 p-4">
                <div class="feature-icon mb-3"><i class="bi bi-journal-text" aria-hidden="true"></i></div>
                <h3 class="h6 fw-semibold">Riwayat aktivitas</h3>
                <p class="text-muted mb-0">Setiap aksi penting tercatat dan tidak bisa diubah.</p>
            </div>
        </div>
    </div>
</main>

<footer class="border-top py-4 text-center text-muted small">
    &copy; {{ date('Y') }} Arsip Kasus. Proyek RPL dengan data fiktif.
</footer>
@endsection
