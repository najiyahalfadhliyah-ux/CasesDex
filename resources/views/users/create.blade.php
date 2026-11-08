@extends('layouts.app')

@section('title', 'Tambah Pengguna - Arsip Kasus')
@section('heading', 'Manajemen Pengguna')

@section('page')
    <a href="{{ route('users.index') }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small mb-3">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Daftar pengguna
    </a>

    <section class="card form-card">
        <div class="card-header"><h1 class="card-title">Tambah pengguna</h1></div>
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('users.store') }}" method="POST" novalidate>
                @csrf
                @include('users._form')

                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus" aria-hidden="true"></i> Simpan pengguna
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </section>
@endsection
