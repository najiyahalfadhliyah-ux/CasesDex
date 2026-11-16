@extends('layouts.app')

@section('title', 'Riwayat Aktivitas - CasesDex')
@section('heading', 'Riwayat Aktivitas')

@section('page')
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Riwayat aktivitas</h1>
        <p class="text-muted mb-0">Jejak setiap aksi penting di sistem. Catatan hanya bisa dilihat, tidak bisa diubah atau dihapus.</p>
    </div>

    <section class="card mb-4" aria-labelledby="filter-title">
        <div class="card-header"><h2 class="card-title" id="filter-title">Saring catatan</h2></div>
        <form method="GET" action="{{ route('audit-logs.index') }}" class="card-body pb-0">
            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <x-select name="user_id" label="Pengguna" :options="$users" :value="$filters['user_id'] ?? null" placeholder="Semua pengguna" />
                </div>
                <div class="col-md-6 col-xl-3">
                    <x-select name="action" label="Jenis aktivitas" :options="\App\Models\AuditLog::ACTIONS" :value="$filters['action'] ?? null" placeholder="Semua aktivitas" />
                </div>
                <div class="col-6 col-xl-2">
                    <x-field name="from" label="Dari tanggal" type="date" :value="$filters['from'] ?? null" />
                </div>
                <div class="col-6 col-xl-2">
                    <x-field name="to" label="Sampai tanggal" type="date" :value="$filters['to'] ?? null" />
                </div>
                <div class="col-xl-2 d-flex align-items-xl-end gap-2 mb-3">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel" aria-hidden="true"></i> Terapkan
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary">Atur ulang</a>
                    @endif
                </div>
            </div>
        </form>
    </section>

    <section class="card" aria-label="Daftar catatan aktivitas">
        <div class="table-responsive">
            <table class="table table-app">
                <thead>
                    <tr>
                        <th scope="col">Waktu</th>
                        <th scope="col">Pengguna</th>
                        <th scope="col">Aktivitas</th>
                        <th scope="col">Rincian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap tabular-nums align-top">
                                <time datetime="{{ $log->created_at->toIso8601String() }}">
                                    <div>{{ $log->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                </time>
                            </td>
                            <td class="align-top">
                                @if ($log->user)
                                    <div class="fw-semibold">{{ $log->user->name }}</div>
                                    <small class="text-muted">{{ $log->user->username }}</small>
                                @else
                                    <span class="text-muted"><i class="bi bi-gear" aria-hidden="true"></i> Sistem</span>
                                @endif
                            </td>
                            <td class="align-top">
                                <div class="fw-semibold">{{ $log->actionLabel() }}</div>
                                @if ($log->subjectLabel())
                                    <small class="text-muted">{{ $log->subjectLabel() }} #{{ $log->auditable_id }}</small>
                                @endif
                            </td>
                            <td class="align-top audit-detail">
                                @if ($log->summary)
                                    <div>{{ $log->summary }}</div>
                                @endif
                                @if ($log->changes)
                                    <x-audit-changes :changes="$log->changes" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="bi bi-journal-text" aria-hidden="true"></i>
                                Tidak ada catatan yang cocok. Ubah atau atur ulang filter untuk melihat catatan lain.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-3">{{ $logs->links() }}</div>
@endsection
