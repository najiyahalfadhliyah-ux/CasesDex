@extends('layouts.guest')

@section('content')
<section class="hero py-5">
    <div class="container py-5 text-center">
        <h1 class="fw-bold mb-3">Sistem Informasi Arsip Kasus dan Dokumentasi Barang Bukti</h1>
        <p class="lead mb-4">Mengarsipkan kasus beserta barang bukti, pihak terkait, dan kronologinya secara rapi dan aman.</p>
        <a href="{{ route('login') }}" class="btn btn-accent btn-lg px-5">Masuk</a>
    </div>
</section>

<section class="container py-5">
    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 p-3">
                <h2 class="h5 fw-semibold">Arsip Kasus</h2>
                <p class="text-muted mb-0">Nomor kasus otomatis dan status penanganan yang jelas.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 p-3">
                <h2 class="h5 fw-semibold">Barang Bukti</h2>
                <p class="text-muted mb-0">Dokumentasi bukti fisik dan digital dalam satu tempat.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 p-3">
                <h2 class="h5 fw-semibold">Integritas SHA-256</h2>
                <p class="text-muted mb-0">Cek keaslian berkas bukti dengan sidik jari SHA-256.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 p-3">
                <h2 class="h5 fw-semibold">Audit Log</h2>
                <p class="text-muted mb-0">Setiap aksi penting tercatat dan tidak dapat diubah.</p>
            </div>
        </div>
    </div>
</section>

<footer class="border-top py-3 text-center text-muted small">
    &copy; {{ date('Y') }} Arsip Kasus &middot; Proyek RPL, data fiktif
</footer>
@endsection
