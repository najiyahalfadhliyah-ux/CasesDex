{{-- Dipakai otomatis oleh Laravel untuk setiap abort(403) --}}
@extends('layouts.guest')

@section('title', 'Akses Ditolak - Arsip Kasus')

@section('content')
<main class="min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="card card-narrow p-4 text-center w-100">
        <p class="display-4 fw-bold mb-2">403</p>
        <h1 class="h5 fw-semibold mb-3">Akses Ditolak</h1>
        <p class="text-muted mb-4">{{ $exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.' }}</p>
        <a href="{{ route('home') }}" class="btn btn-accent">Kembali</a>
    </div>
</main>
@endsection
