{{-- Dipakai otomatis oleh Laravel untuk setiap abort(403) --}}
@extends('layouts.guest')

@section('title', 'Akses Ditolak - CasesDex')

@section('content')
<main class="min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="card card-narrow p-4 p-sm-5 text-center w-100">
        <i class="bi bi-shield-lock fs-1 mb-2" style="color:var(--accent)" aria-hidden="true"></i>
        <p class="error-code mb-2">403</p>
        <h1 class="h4 fw-bold mb-2">Akses ditolak</h1>
        <p class="text-muted mb-4">{{ $exception->getMessage() ?: 'Akun Anda tidak memiliki izin untuk membuka halaman ini.' }}</p>
        <a href="{{ route('home') }}" class="btn btn-primary">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali
        </a>
    </div>
</main>
@endsection
