@extends('layouts.app')

@section('title', "{$case->case_number} - CasesDex")
@section('heading', 'Kasus')

@section('page')
    <a href="{{ route('cases.index') }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small mb-3">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Daftar kasus
    </a>

    <x-flash />

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <p class="case-number fs-5 mb-1">{{ $case->case_number }}</p>
            <h1 class="h3 fw-bold mb-2">{{ $case->title }}</h1>
            <x-status-badge :status="$case->status" />
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('update', $case)
                <a href="{{ route('cases.edit', $case) }}" class="btn btn-primary">
                    <i class="bi bi-pencil" aria-hidden="true"></i> Ubah kasus
                </a>
            @endcan
            @can('delete', $case)
                <form action="{{ route('cases.destroy', $case) }}" method="POST"
                      data-confirm="Nonaktifkan kasus {{ $case->case_number }}? Kasus tidak tampil lagi di daftar, tetapi datanya tetap tersimpan.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-archive" aria-hidden="true"></i> Nonaktifkan
                    </button>
                </form>
            @endcan
        </div>
    </div>

    @can('update', $case)
        <section class="card mb-3">
            <div class="card-header"><h2 class="card-title">Status penanganan</h2></div>
            <div class="card-body">
                <form action="{{ route('cases.status', $case) }}" method="POST" novalidate class="row gx-3 align-items-start">
                    @csrf
                    @method('PATCH')
                    <div class="col-md-4">
                        <x-select name="status" label="Status" :options="App\Models\CaseRecord::STATUSES" :value="$case->status" required />
                    </div>
                    <div class="col-md-4">
                        <x-field name="completed_date" label="Tanggal selesai" type="date"
                                 :value="$case->completed_date?->format('Y-m-d')" help="Wajib diisi jika status Selesai." />
                    </div>
                    <div class="col-md-4 pt-md-4 mt-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2" aria-hidden="true"></i> Simpan status
                        </button>
                    </div>
                </form>
            </div>
        </section>
    @endcan

    <div class="row g-3">
        <div class="col-lg-5">
            <section class="card h-100">
                <div class="card-header"><h2 class="card-title">Identitas kasus</h2></div>
                <div class="card-body">
                    <dl class="detail-list">
                        <dt>Kategori</dt><dd>{{ $case->category }}</dd>
                        <dt>Lokasi kejadian</dt><dd>{{ $case->location ?? '-' }}</dd>
                        <dt>Tanggal kejadian</dt><dd>{{ $case->incident_date->format('d/m/Y') }}</dd>
                        <dt>Tanggal pencatatan</dt><dd>{{ $case->recorded_date->format('d/m/Y') }}</dd>
                        <dt>Mulai investigasi</dt><dd>{{ $case->investigation_start_date->format('d/m/Y') }}</dd>
                        <dt>Tanggal selesai</dt><dd>{{ $case->completed_date?->format('d/m/Y') ?? '-' }}</dd>
                        <dt>Tenggat arsip</dt><dd>{{ $case->archive_deadline->format('d/m/Y') }}</dd>
                        <dt>Petugas</dt><dd>{{ $case->officer?->name ?? 'Belum ditugaskan' }}</dd>
                        <dt>Dicatat oleh</dt><dd>{{ $case->creator?->name ?? '-' }}</dd>
                    </dl>
                </div>
            </section>
        </div>

        <div class="col-lg-7">
            <section class="card h-100">
                <div class="card-header"><h2 class="card-title">Uraian</h2></div>
                <div class="card-body">
                    <h3 class="h6 fw-semibold">Deskripsi singkat</h3>
                    <p class="text-pre-line">{{ $case->description ?: 'Belum diisi.' }}</p>

                    <h3 class="h6 fw-semibold">Ringkasan kasus</h3>
                    <p class="text-pre-line {{ $case->summary ? '' : 'text-muted' }}">{{ $case->summary ?: 'Belum diisi.' }}</p>

                    <h3 class="h6 fw-semibold">Dugaan motif</h3>
                    <p class="text-pre-line mb-0 {{ $case->suspected_motive ? '' : 'text-muted' }}">{{ $case->suspected_motive ?: 'Belum diisi.' }}</p>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Konfirmasi sebelum menonaktifkan. Teks diambil dari data-confirm (sudah di-escape Blade).
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
</script>
@endpush
