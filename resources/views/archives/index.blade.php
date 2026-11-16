@extends('layouts.app')

@section('title', 'Arsip Kasus - CasesDex')
@section('heading', 'Arsip Kasus')

@section('page')
    @php($isAdmin = auth()->user()->role === 'admin')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Arsip kasus</h1>
            <p class="text-muted mb-0">
                {{ $isAdmin ? 'Kasus yang sudah diarsipkan. Buka arsip dari halaman detail kasus.' : 'Arsip kasus yang ditugaskan kepada Anda.' }}
            </p>
        </div>
        <a href="{{ route('cases.index', ['archive_status' => 'diarsipkan']) }}" class="btn btn-outline-secondary">
            <i class="bi bi-search" aria-hidden="true"></i> Cari di arsip
        </a>
    </div>

    <x-flash />

    <section class="card" aria-label="Daftar arsip kasus">
        <div class="table-responsive">
            <table class="table table-app">
                <thead>
                    <tr>
                        <th scope="col">No. kasus</th>
                        <th scope="col">Judul</th>
                        <th scope="col">Status</th>
                        @if ($isAdmin)
                            <th scope="col" class="d-none d-md-table-cell">Petugas</th>
                        @endif
                        <th scope="col">Tgl. arsip</th>
                        <th scope="col">Alasan arsip</th>
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
                            <td class="text-nowrap"><x-status-badge :status="$case->status" /></td>
                            @if ($isAdmin)
                                <td class="d-none d-md-table-cell">{{ $case->officer?->name ?? 'Belum ditugaskan' }}</td>
                            @endif
                            <td class="text-nowrap tabular-nums">{{ $case->archived_at?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ App\Models\CaseRecord::ARCHIVE_REASONS[$case->archive_reason] ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('cases.show', $case) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye" aria-hidden="true"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 7 : 6 }}" class="empty-state">
                                <i class="bi bi-archive" aria-hidden="true"></i>
                                @if ($isAdmin)
                                    Belum ada kasus yang diarsipkan. Arsipkan kasus dari halaman detail kasus.
                                @else
                                    Belum ada arsip kasus yang ditugaskan kepada Anda.
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
