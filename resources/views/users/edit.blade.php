@extends('layouts.app')

@section('title', 'Ubah Pengguna - CasesDex')
@section('heading', 'Manajemen Pengguna')

@section('page')
    <a href="{{ route('users.index') }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small mb-3">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Daftar pengguna
    </a>

    <section class="card form-card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h1 class="card-title">Ubah pengguna: {{ $user->name }}</h1>
            <span class="badge-status {{ $user->is_active ? 'badge-aktif' : 'badge-nonaktif' }}">
                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('users.update', $user) }}" method="POST" novalidate>
                @csrf
                @method('PUT')
                @include('users._form')

                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2" aria-hidden="true"></i> Simpan perubahan
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </section>
@endsection
