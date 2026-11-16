@extends('layouts.app')

@section('title', 'Dashboard - CasesDex')
@section('heading', 'Dashboard')

@use('App\Models\CaseRecord')
@php
    $isAdmin = auth()->user()->role === 'admin';
    $summary = $stats['summary'];
    $hasData = $summary['total'] > 0;
    // Teks alternatif grafik: "label n kasus, ..."
    $describe = fn (array $rows, array $labels = []) => collect($rows)->map(fn ($n, $k) => ($labels[$k] ?? $k)." {$n} kasus")->implode(', ');
@endphp

@section('page')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Selamat datang, {{ auth()->user()->name }}</h1>
            <p class="text-muted mb-0">{{ $isAdmin ? 'Ringkasan seluruh kasus.' : 'Ringkasan kasus yang ditugaskan kepada Anda.' }}</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i> Statistik dan laporan
        </a>
    </div>

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

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <section class="card h-100" aria-labelledby="judul-tahun">
                <div class="card-header"><h2 class="card-title" id="judul-tahun">Kasus per tahun kejadian</h2></div>
                <div class="card-body">
                    @if ($hasData)
                        <div class="chart-box">
                            <canvas id="chartTahun" role="img" aria-label="Grafik batang jumlah kasus per tahun: {{ $describe($stats['by_year']) }}."></canvas>
                        </div>
                        <details class="chart-data">
                            <summary>Lihat data sebagai tabel</summary>
                            <div class="table-responsive"><x-stat-table :rows="$stats['by_year']" label="Tahun" caption="Kasus per tahun kejadian" /></div>
                        </details>
                    @else
                        <div class="empty-state"><i class="bi bi-bar-chart" aria-hidden="true"></i> Grafik muncul setelah ada data kasus.</div>
                    @endif
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="card h-100" aria-labelledby="judul-kategori">
                <div class="card-header"><h2 class="card-title" id="judul-kategori">Kasus per kategori</h2></div>
                <div class="card-body">
                    @if ($hasData)
                        <div class="chart-box">
                            <canvas id="chartKategori" role="img" aria-label="Grafik lingkaran kasus per kategori: {{ $describe($stats['by_category']) }}."></canvas>
                        </div>
                        <details class="chart-data">
                            <summary>Lihat data sebagai tabel</summary>
                            <div class="table-responsive"><x-stat-table :rows="$stats['by_category']" label="Kategori" caption="Kasus per kategori" /></div>
                        </details>
                    @else
                        <div class="empty-state"><i class="bi bi-pie-chart" aria-hidden="true"></i> Grafik muncul setelah ada data kasus.</div>
                    @endif
                </div>
            </section>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <section class="card h-100" aria-labelledby="judul-status">
                <div class="card-header"><h2 class="card-title" id="judul-status">Kasus per status</h2></div>
                <div class="card-body">
                    @if ($hasData)
                        <div class="chart-box">
                            <canvas id="chartKasus" role="img" aria-label="Grafik batang kasus per status: {{ $describe($stats['by_status'], CaseRecord::STATUSES) }}."></canvas>
                        </div>
                        <details class="chart-data">
                            <summary>Lihat data sebagai tabel</summary>
                            <div class="table-responsive"><x-stat-table :rows="$stats['by_status']" label="Status" :labels="CaseRecord::STATUSES" caption="Kasus per status" /></div>
                        </details>
                    @else
                        <div class="empty-state"><i class="bi bi-bar-chart" aria-hidden="true"></i> Grafik muncul setelah ada data kasus.</div>
                    @endif
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="card h-100" aria-labelledby="judul-tenggat">
                <div class="card-header">
                    <h2 class="card-title" id="judul-tenggat">Pengingat tenggat arsip</h2>
                    <p class="text-muted small mb-0 mt-1">Kasus aktif dengan tenggat 30 hari lagi atau sudah lewat. Hanya informasi.</p>
                </div>
                <div class="card-body">
                    @if ($reminders->isNotEmpty())
                        <ul class="list-unstyled reminder-list mb-0">
                            @foreach ($reminders as $case)
                                @php($days = (int) today()->diffInDays($case->archive_deadline, false))
                                <li>
                                    <div>
                                        <a href="{{ route('cases.show', $case) }}" class="case-number text-decoration-none">{{ $case->case_number }}</a>
                                        <div class="small text-break">{{ $case->title }}</div>
                                        <div class="small text-muted tabular-nums">Tenggat {{ $case->archive_deadline->format('d/m/Y') }}</div>
                                    </div>
                                    @if ($days < 0)
                                        <span class="badge-status badge-tenggat-lewat text-nowrap">Lewat {{ abs($days) }} hari</span>
                                    @else
                                        <span class="badge-status badge-tenggat-dekat text-nowrap">{{ $days === 0 ? 'Hari ini' : "Sisa {$days} hari" }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="empty-state">
                            <i class="bi bi-calendar-check" aria-hidden="true"></i>
                            Tidak ada kasus yang mendekati tenggat arsip.
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>

    <section class="card" aria-labelledby="judul-terbaru">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h2 class="card-title" id="judul-terbaru">Kasus terbaru</h2>
            <a href="{{ route('cases.index') }}" class="small">Lihat semua kasus</a>
        </div>
        <div class="table-responsive">
            <table class="table table-app mb-0">
                <thead>
                    <tr>
                        <th scope="col">No. kasus</th>
                        <th scope="col">Judul</th>
                        <th scope="col">Status</th>
                        @if ($isAdmin)
                            <th scope="col" class="d-none d-md-table-cell">Petugas</th>
                        @endif
                        <th scope="col" class="d-none d-md-table-cell">Tgl. kejadian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latest as $case)
                        <tr>
                            <td class="text-nowrap">
                                <a href="{{ route('cases.show', $case) }}" class="case-number text-decoration-none">{{ $case->case_number }}</a>
                            </td>
                            <td>{{ $case->title }}</td>
                            <td class="text-nowrap">
                                <x-status-badge :status="$case->status" />
                                @if ($case->archive_status === 'diarsipkan')
                                    <span class="badge-status badge-arsip">Diarsipkan</span>
                                @endif
                            </td>
                            @if ($isAdmin)
                                <td class="d-none d-md-table-cell">{{ $case->officer?->name ?? 'Belum ditugaskan' }}</td>
                            @endif
                            <td class="d-none d-md-table-cell text-nowrap tabular-nums">{{ $case->incident_date->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 5 : 4 }}" class="empty-state">
                                <i class="bi bi-inbox" aria-hidden="true"></i>
                                {{ $isAdmin ? 'Belum ada kasus. Tambahkan kasus dari menu Kasus.' : 'Belum ada kasus yang ditugaskan kepada Anda.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@if ($hasData)
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        // Warna diambil dari variabel CSS di app.css, jadi grafik ikut sistem desain
        const css = getComputedStyle(document.documentElement);
        const token = (name) => css.getPropertyValue(name).trim();

        Chart.defaults.font.family = token('--font-sans');
        Chart.defaults.color = token('--muted');
        if (matchMedia('(prefers-reduced-motion: reduce)').matches) Chart.defaults.animation = false;

        // Data dari server (direktif json Blade meng-escape karakter berbahaya untuk konteks script)
        const byYear = @json($stats['by_year']);
        const byCategory = @json($stats['by_category']);
        const byStatus = @json($stats['by_status']);
        const statusLabels = @json(CaseRecord::STATUSES);

        // Satu seri: tanpa legenda (judul kartu sudah menamai), sumbu y bilangan bulat, grid samar
        const barOptions = {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: token('--border') } },
            },
        };
        const bar = { borderRadius: 4, maxBarThickness: 48 };

        new Chart(document.getElementById('chartTahun'), {
            type: 'bar',
            data: { labels: Object.keys(byYear), datasets: [{ label: 'Jumlah kasus', data: Object.values(byYear), backgroundColor: token('--primary'), ...bar }] },
            options: barOptions,
        });

        // Warna kategori mengikuti urutan konstanta (tetap per kategori, tidak berubah walau datanya 0)
        new Chart(document.getElementById('chartKategori'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(byCategory),
                datasets: [{
                    label: 'Jumlah kasus',
                    data: Object.values(byCategory),
                    backgroundColor: Object.keys(byCategory).map((_, i) => token('--chart-' + (i + 1))),
                    borderColor: token('--surface'), borderWidth: 2,
                }],
            },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } } } },
        });

        // Status: warna status yang sama dengan badge; nama status ada di sumbu x, jadi tidak bergantung warna
        new Chart(document.getElementById('chartKasus'), {
            type: 'bar',
            data: {
                labels: Object.keys(byStatus).map((k) => statusLabels[k] ?? k),
                datasets: [{
                    label: 'Jumlah kasus',
                    data: Object.values(byStatus),
                    backgroundColor: Object.keys(byStatus).map((k) => token('--status-' + k.replaceAll('_', '-'))),
                    ...bar,
                }],
            },
            options: barOptions,
        });
    </script>
    @endpush
@endif
