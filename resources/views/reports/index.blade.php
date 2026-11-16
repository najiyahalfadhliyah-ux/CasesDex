@extends('layouts.app')

@section('title', 'Statistik dan Laporan - CasesDex')
@section('heading', 'Statistik dan Laporan')

@use('App\Models\CaseRecord')
@use('Illuminate\Support\Carbon')
@php
    $from = isset($filters['from']) ? Carbon::parse($filters['from'])->format('d/m/Y') : null;
    $to = isset($filters['to']) ? Carbon::parse($filters['to'])->format('d/m/Y') : null;
    $period = match (true) {
        $from && $to => "{$from} sampai {$to}",
        (bool) $from => "Sejak {$from}",
        (bool) $to => "Sampai {$to}",
        default => 'Semua periode',
    };
    $summary = $stats['summary'];
@endphp

@section('page')
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Laporan ringkas kasus</h1>
            <p class="text-muted mb-0">
                Periode tanggal kejadian: <strong>{{ $period }}</strong>.
                {{ auth()->user()->role === 'admin' ? 'Mencakup seluruh kasus.' : 'Mencakup kasus yang ditugaskan kepada Anda.' }}
            </p>
            <p class="text-muted small mb-0">Dibuat {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}.</p>
        </div>
        <button type="button" class="btn btn-primary no-print" id="btn-cetak">
            <i class="bi bi-printer" aria-hidden="true"></i> Cetak laporan
        </button>
    </div>

    <section class="card mb-4 no-print" aria-labelledby="filter-title">
        <div class="card-header"><h2 class="card-title" id="filter-title">Periode laporan</h2></div>
        <form method="GET" action="{{ route('reports.index') }}" class="card-body pb-0">
            <div class="row g-3">
                <div class="col-sm-6 col-lg-4">
                    <x-field name="from" label="Dari tanggal kejadian" type="date" :value="$filters['from'] ?? null" />
                </div>
                <div class="col-sm-6 col-lg-4">
                    <x-field name="to" label="Sampai tanggal kejadian" type="date" :value="$filters['to'] ?? null" />
                </div>
                <div class="col-lg-4 d-flex align-items-lg-end gap-2 mb-3">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel" aria-hidden="true"></i> Terapkan
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Atur ulang</a>
                    @endif
                </div>
            </div>
        </form>
    </section>

    <div class="row g-3 mb-4">
        @foreach ([
            ['bi-folder2', 'Total kasus', $summary['total']],
            ['bi-hourglass-split', 'Dalam penanganan', $summary['dalam_penanganan']],
            ['bi-check2-circle', 'Selesai', $summary['selesai']],
            ['bi-archive', 'Diarsipkan', $summary['diarsipkan']],
        ] as [$icon, $label, $value])
            <div class="col-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="stat-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></div>
                    <div>
                        <div class="stat-value">{{ $value }}</div>
                        <div class="stat-label">{{ $label }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-md-6 col-xl-4">
            <section class="card h-100" aria-labelledby="judul-kategori">
                <div class="card-header"><h2 class="card-title" id="judul-kategori">Per kategori</h2></div>
                <div class="table-responsive"><x-stat-table :rows="$summary['total'] ? $stats['by_category'] : []" label="Kategori" /></div>
            </section>
        </div>
        <div class="col-md-6 col-xl-4">
            <section class="card h-100" aria-labelledby="judul-status">
                <div class="card-header"><h2 class="card-title" id="judul-status">Per status penanganan</h2></div>
                <div class="table-responsive"><x-stat-table :rows="$summary['total'] ? $stats['by_status'] : []" label="Status" :labels="CaseRecord::STATUSES" /></div>
            </section>
        </div>
        <div class="col-md-6 col-xl-4">
            <section class="card h-100" aria-labelledby="judul-tahun">
                <div class="card-header"><h2 class="card-title" id="judul-tahun">Per tahun kejadian</h2></div>
                <div class="table-responsive"><x-stat-table :rows="$stats['by_year']" label="Tahun" /></div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('btn-cetak').addEventListener('click', () => window.print());
</script>
@endpush
