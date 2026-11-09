@extends('layouts.app')

@section('title', 'Tambah Kasus - CasesDex')
@section('heading', 'Kasus')

@section('page')
    <a href="{{ route('cases.index') }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small mb-3">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Daftar kasus
    </a>

    <section class="card form-card">
        <div class="card-header">
            <h1 class="card-title">Tambah kasus</h1>
            <p class="text-muted small mb-0 mt-1">Nomor kasus dan tanggal pencatatan dibuat otomatis saat disimpan.</p>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('cases.store') }}" method="POST" novalidate>
                @csrf
                @include('cases._form')

                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-folder-plus" aria-hidden="true"></i> Simpan kasus
                    </button>
                    <a href="{{ route('cases.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </section>
@endsection
