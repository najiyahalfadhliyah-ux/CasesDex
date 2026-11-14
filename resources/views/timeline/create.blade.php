@extends('layouts.app')

@section('title', 'Tambah Peristiwa - CasesDex')
@section('heading', 'Kronologi')

@section('page')
    <a href="{{ route('cases.timeline-events.index', $case) }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small mb-3">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke kronologi
    </a>

    <section class="card form-card">
        <div class="card-header">
            <h1 class="card-title">Tambah peristiwa</h1>
            <p class="text-muted small mb-0 mt-1">
                Untuk kasus <span class="case-number">{{ $case->case_number }}</span> &middot; {{ $case->title }}
            </p>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('cases.timeline-events.store', $case) }}" method="POST" novalidate>
                @csrf
                @include('timeline._form')

                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i> Simpan peristiwa
                    </button>
                    <a href="{{ route('cases.timeline-events.index', $case) }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </section>
@endsection
