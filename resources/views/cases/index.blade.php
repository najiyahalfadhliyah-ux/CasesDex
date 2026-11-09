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

    <section class="card">
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
                            <td class="text-nowrap"><x-status-badge :status="$case->status" /></td>
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
                                @can('create', App\Models\CaseRecord::class)
                                    Belum ada kasus. Tambahkan kasus pertama dengan tombol di atas.
                                @else
                                    Belum ada kasus yang ditugaskan kepada Anda.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-3">{{ $cases->links() }}</div>
@endsection
