@extends('layouts.app')

@section('title', "Ubah {$case->case_number} - CasesDex")
@section('heading', 'Kasus')

@section('page')
    <a href="{{ route('cases.show', $case) }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small mb-3">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Detail kasus
    </a>

    <section class="card form-card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h1 class="card-title">Ubah kasus <span class="case-number">{{ $case->case_number }}</span></h1>
            <x-status-badge :status="$case->status" />
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('cases.update', $case) }}" method="POST" novalidate>
                @csrf
                @method('PUT')
                @include('cases._form')

                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2" aria-hidden="true"></i> Simpan perubahan
                    </button>
                    <a href="{{ route('cases.show', $case) }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </section>
@endsection
