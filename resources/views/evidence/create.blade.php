@extends('layouts.app')

@php($isDigital = $evidence->type === 'digital')

@section('title', ($isDigital ? 'Unggah Bukti Digital' : 'Tambah Bukti Fisik').' - CasesDex')
@section('heading', 'Barang bukti')

@section('page')
    <a href="{{ route('cases.show', $case) }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small mb-3">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke kasus
    </a>

    <section class="card form-card">
        <div class="card-header">
            <h1 class="card-title">{{ $isDigital ? 'Unggah bukti digital' : 'Tambah bukti fisik' }}</h1>
            <p class="text-muted small mb-0 mt-1">
                Untuk kasus <span class="case-number">{{ $case->case_number }}</span> &middot; {{ $case->title }}
            </p>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('cases.evidence.store', $case) }}" method="POST" enctype="multipart/form-data" novalidate
                  data-uploading="{{ $isDigital ? 'Mengunggah dan menghitung hash...' : 'Menyimpan...' }}">
                @csrf
                <input type="hidden" name="type" value="{{ $evidence->type }}">
                @include('evidence._form')

                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi {{ $isDigital ? 'bi-upload' : 'bi-box-seam' }}" aria-hidden="true"></i>
                        {{ $isDigital ? 'Unggah bukti' : 'Simpan bukti' }}
                    </button>
                    <a href="{{ route('cases.show', $case) }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </section>
@endsection
