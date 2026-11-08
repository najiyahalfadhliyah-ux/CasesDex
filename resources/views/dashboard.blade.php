@extends('layouts.app')

@section('title', 'Dashboard - Arsip Kasus')
@section('heading', 'Dashboard')

@section('page')
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="text-muted mb-0">
            {{ auth()->user()->role === 'admin' ? 'Ringkasan seluruh kasus.' : 'Ringkasan kasus yang ditugaskan kepada Anda.' }}
        </p>
    </div>

    {{-- Kartu statistik: angka diisi di Tahap 9 --}}
    <div class="row g-3 mb-4">
        @foreach ([
            ['bi-folder2', 'Total kasus'],
            ['bi-hourglass-split', 'Dalam penanganan'],
            ['bi-check2-circle', 'Selesai'],
            ['bi-box-seam', 'Barang bukti'],
        ] as [$icon, $label])
            <div class="col-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="stat-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></div>
                    <div>
                        <div class="stat-value">&ndash;</div>
                        <div class="stat-label">{{ $label }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <section class="card h-100">
                <div class="card-header"><h2 class="card-title">Kasus per status</h2></div>
                <div class="card-body">
                    {{-- Chart.js menggambar di canvas ini (Tahap 9) --}}
                    <canvas id="chartKasus" height="120" hidden></canvas>
                    <div class="empty-state">
                        <i class="bi bi-bar-chart" aria-hidden="true"></i>
                        Grafik muncul setelah ada data kasus.
                    </div>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <span class="badge-status badge-dalam_penanganan">Dalam penanganan</span>
                        <span class="badge-status badge-selesai">Selesai</span>
                        <span class="badge-status badge-ditutup">Ditutup</span>
                        <span class="badge-status badge-ditangguhkan">Ditangguhkan</span>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="card h-100">
                <div class="card-header"><h2 class="card-title">Pengingat tenggat arsip</h2></div>
                <div class="card-body">
                    <div class="empty-state">
                        <i class="bi bi-calendar-check" aria-hidden="true"></i>
                        Tidak ada kasus yang mendekati tenggat arsip.
                    </div>
                </div>
            </section>
        </div>
    </div>

    <section class="card">
        <div class="card-header"><h2 class="card-title">Kasus terbaru</h2></div>
        <div class="table-responsive">
            <table class="table table-app">
                <thead>
                    <tr>
                        <th scope="col">No. kasus</th>
                        <th scope="col">Judul</th>
                        <th scope="col">Status</th>
                        <th scope="col">Petugas</th>
                        <th scope="col">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="bi bi-inbox" aria-hidden="true"></i>
                            Belum ada kasus untuk ditampilkan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
@endsection
