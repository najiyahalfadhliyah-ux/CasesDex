@extends('layouts.app')

@section('title', 'Ubah Pihak Terkait - CasesDex')
@section('heading', 'Pihak terkait')

@section('page')
    <a href="{{ route('cases.show', $case) }}#pihak-terkait" class="d-inline-flex align-items-center gap-1 text-decoration-none small mb-3">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke kasus
    </a>

    <section class="card form-card">
        <div class="card-header">
            <h1 class="card-title">Ubah pihak terkait</h1>
            <p class="text-muted small mb-0 mt-1">
                Untuk kasus <span class="case-number">{{ $case->case_number }}</span> &middot; {{ $case->title }}
            </p>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('suspects.update', $suspect) }}" method="POST" novalidate>
                @csrf
                @method('PUT')
                @include('suspects._form')

                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2" aria-hidden="true"></i> Simpan perubahan
                    </button>
                    <a href="{{ route('cases.show', $case) }}#pihak-terkait" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </section>
@endsection
