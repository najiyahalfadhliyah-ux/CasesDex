@extends('layouts.app')

@section('title', 'Kasus - CasesDex')
@section('heading', 'Kasus')

@section('page')
    @php($isAdmin = auth()->user()->role === 'admin')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Daftar kasus</h1>
            <p class="text-muted mb-0">
                {{ $isAdmin ?'Semua kasus yang tercatat.' : 'Kasus yang ditugaskan kepada Anda.' }}
            </p>
        </div>
        @can('create', App\Models\CaseRecord::class)
            <a href="{{ route('cases.create') }}" class="btn btn-primary">
                <i class="bi bi-folder-plus" aria-hidden="true"></i> Tambah kasus
            </a>
        @endcan
    </div>

    <x-flash />

    @php($filtered = array_filter($filters))
    <section class="card mb-4" aria-labelledby="filter-title">
        <div class="card-header"><h2 class="card-title" id="filter-title">Cari dan saring kasus</h2></div>
        <form method="GET" action="{{ route('cases.index') }}" class="card-body pb-0" role="search">
            <div class="row g-3">
                <div class="col-lg-6">
                    <x-field name="q" label="Kata kunci" type="search" :value="$filters['q'] ?? null" maxlength="100"
                             help="Nomor kasus, judul kasus, nama bukti, atau nama berkas." />
                </div>
                <div class="col-sm-6 col-lg-3">
                    <x-select name="category" label="Kategori" :options="array_combine(App\Models\CaseRecord::CATEGORIES, App\Models\CaseRecord::CATEGORIES)"
                              :value="$filters['category'] ?? null" placeholder="Semua kategori" />
                </div>
                <div class="col-sm-6 col-lg-3">
                    <x-field name="year" label="Tahun kejadian" type="number" :value="$filters['year'] ?? null" min="2000" max="2100" inputmode="numeric" />
                </div>
                <div class="col-sm-6 col-lg-3">
                    <x-select name="status" label="Status penanganan" :options="App\Models\CaseRecord::STATUSES"
                              :value="$filters['status'] ?? null" placeholder="Semua status" />
                </div>
                <div class="col-sm-6 col-lg-3">
                    <x-select name="archive_status" label="Status arsip" :options="App\Models\CaseRecord::ARCHIVE_STATUSES + ['semua' => 'Aktif dan diarsipkan']"
                              :value="$filters['archive_status'] ?? 'aktif'" />
                </div>
                <div class="col-sm-6 col-lg-3">
                    <x-select name="evidence_type" label="Jenis barang bukti" :options="App\Models\Evidence::TYPES"
                              :value="$filters['evidence_type'] ?? null" placeholder="Semua jenis" />
                </div>
                <div class="col-sm-6 col-lg-3 d-flex align-items-lg-end gap-2 mb-3">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search" aria-hidden="true"></i> Cari
                    </button>
                    @if ($filtered)
                        <a href="{{ route('cases.index') }}" class="btn btn-outline-secondary">Atur ulang</a>
                    @endif
                </div>
            </div>
        </form>
    </section>

    @if ($filtered)
        <p class="text-muted mb-2" role="status">{{ $cases->total() }} kasus ditemukan.</p>
    @endif

    <section class="card" aria-label="Daftar kasus">
        <div class="table-responsive">
            <table class="table table-app">
                <thead>
                    <tr>
                        <th scope="col">No. kasus</th>
                        <th scope="col">Judul</th>
                        <th scope="col" class="d-none d-lg-table-cell">Kategori</th>
                        <th scope="col">Status</th>
                        {{-- Petugas hanya melihat kasusnya sendiri, jadi kolom ini khusus admin --}}
                        @if ($isAdmin)
                            <th scope="col" class="d-none d-md-table-cell">Petugas</th>
                        @endif
                        <th scope="col" class="d-none d-md-table-cell">Tgl. kejadian</th>
                        <th scope="col"><span class="visually-hidden">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cases as $case)
                        <tr>
                            <td class="text-nowrap">
                                <a href="{{ route('cases.show', $case) }}" class="case-number text-decoration-none">{{ $case->case_number }}</a>
                            </td>
                            <td>{{ $case->title }}</td>
                            <td class="d-none d-lg-table-cell">{{ $case->category }}</td>
                            <td class="text-nowrap">
                                <x-status-badge :status="$case->status" />
                                @if ($case->archive_status === 'diarsipkan')
                                    <span class="badge-status badge-arsip">Diarsipkan</span>
                                @endif
                            </td>
                            @if ($isAdmin)
                                <td class="d-none d-md-table-cell">{{ $case->officer?->name ?? 'Belum ditugaskan' }}</td>
                            @endif
                            <td class="d-none d-md-table-cell text-nowrap">{{ $case->incident_date->format('d/m/Y') }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('cases.show', $case) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye" aria-hidden="true"></i> Detail
                                </a>
                                @can('update', $case)
                                    <a href="{{ route('cases.edit', $case) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil" aria-hidden="true"></i> Ubah
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 7 : 6 }}" class="empty-state">
                                <i class="bi bi-inbox" aria-hidden="true"></i>
                                @if ($filtered)
                                    Tidak ada kasus yang cocok. Ubah kata kunci atau atur ulang filter.
                                @elseif (auth()->user()->can('create', App\Models\CaseRecord::class))
                                    Belum ada kasus. Tambahkan kasus pertama dengan tombol di atas.
                                @else
                                    Belum ada kasus aktif yang ditugaskan kepada Anda.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-3">{{ $cases->links() }}</div>
@endsection
