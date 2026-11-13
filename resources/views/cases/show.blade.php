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

    <section class="card mt-3" aria-labelledby="judul-bukti">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h2 class="card-title" id="judul-bukti">Barang bukti ({{ $case->evidence->count() }})</h2>
            @can('update', $case)
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('cases.evidence.create', [$case, 'type' => 'fisik']) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-box-seam" aria-hidden="true"></i> Tambah bukti fisik
                    </a>
                    <a href="{{ route('cases.evidence.create', [$case, 'type' => 'digital']) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-upload" aria-hidden="true"></i> Unggah bukti digital
                    </a>
                </div>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-app mb-0">
                <thead>
                    <tr>
                        <th scope="col">Nama bukti</th>
                        <th scope="col">Jenis</th>
                        <th scope="col">Tanggal pencatatan</th>
                        <th scope="col">Metadata</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="visually-hidden">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($case->evidence as $item)
                        <tr>
                            <td class="fw-medium text-break">{{ $item->name }}</td>
                            <td>{{ App\Models\Evidence::TYPES[$item->type] ?? $item->type }}</td>
                            <td class="text-nowrap">{{ $item->recorded_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="small">
                                @if ($item->type === 'digital')
                                    {{ $item->format }} &middot; {{ $item->sizeLabel() }}<br>
                                    <span class="hash-value text-muted" title="{{ $item->sha256_hash }}">SHA-256 {{ Str::limit($item->sha256_hash, 16, '…') }}</span>
                                @else
                                    {{ $item->storage_location ?? '-' }}
                                    @if ($item->photo_path) <br><span class="text-muted"><i class="bi bi-image" aria-hidden="true"></i> Ada foto</span> @endif
                                @endif
                            </td>
                            <td>
                                <span class="badge-status {{ $item->is_active ? 'badge-aktif' : 'badge-nonaktif' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('evidence.show', $item) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-eye" aria-hidden="true"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="bi bi-box-seam" aria-hidden="true"></i>
                                Belum ada barang bukti. Tambahkan bukti fisik atau unggah bukti digital dengan tombol di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
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
