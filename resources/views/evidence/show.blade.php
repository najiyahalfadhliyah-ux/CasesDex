@extends('layouts.app')

@section('title', "{$evidence->name} - CasesDex")
@section('heading', 'Barang bukti')

@section('page')
    @php($case = $evidence->case)
    @php($latestCheck = $evidence->hashChecks->first())
    {{-- Link unduh bertanda tangan, berlaku 30 menit. Setelah itu muat ulang halaman untuk link baru. --}}
    @php($downloadUrl = fn ($version = null) => URL::temporarySignedRoute('evidence.download', now()->addMinutes(30), array_filter([$evidence, $version])))

    <a href="{{ route('cases.show', $case) }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small mb-3">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Kasus {{ $case->case_number }}
    </a>

    <x-flash />

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <p class="text-muted mb-1">
                <i class="bi {{ $evidence->type === 'digital' ? 'bi-file-earmark-binary' : 'bi-box-seam' }}" aria-hidden="true"></i>
                Bukti {{ $evidence->type }} &middot; <span class="case-number">{{ $case->case_number }}</span>
            </p>
            <h1 class="h3 fw-bold mb-2 text-break">{{ $evidence->name }}</h1>
            <span class="badge-status {{ $evidence->is_active ? 'badge-aktif' : 'badge-nonaktif' }}">
                {{ $evidence->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if ($evidence->file_path || $evidence->photo_path)
                <a href="{{ $downloadUrl() }}" class="btn btn-outline-secondary">
                    <i class="bi bi-download" aria-hidden="true"></i> {{ $evidence->type === 'digital' ? 'Unduh berkas asli' : 'Unduh foto' }}
                </a>
            @endif
            @can('update', $evidence)
                <a href="{{ route('evidence.edit', $evidence) }}" class="btn btn-primary">
                    <i class="bi bi-pencil" aria-hidden="true"></i> Ubah bukti
                </a>
            @endcan
            @can('toggleActive', $evidence)
                <form action="{{ route('evidence.toggle-active', $evidence) }}" method="POST"
                      data-confirm="{{ $evidence->is_active ? "Nonaktifkan bukti {$evidence->name}? Bukti tetap tersimpan, tetapi tidak bisa diubah atau diperiksa." : "Aktifkan kembali bukti {$evidence->name}?" }}">
                    @csrf
                    @method('PATCH')
                    @if ($evidence->is_active)
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-slash-circle" aria-hidden="true"></i> Nonaktifkan
                        </button>
                    @else
                        <button type="submit" class="btn btn-outline-success">
                            <i class="bi bi-check-circle" aria-hidden="true"></i> Aktifkan
                        </button>
                    @endif
                </form>
            @endcan
        </div>
    </div>

    <div class="row g-3">
        <div class="{{ $evidence->type === 'digital' ? 'col-lg-7' : 'col-lg-8' }}">
            <section class="card h-100">
                <div class="card-header"><h2 class="card-title">Metadata bukti</h2></div>
                <div class="card-body">
                    <dl class="detail-list">
                        <dt>Kasus</dt><dd>{{ $case->title }}</dd>
                        <dt>Jenis</dt><dd>{{ App\Models\Evidence::TYPES[$evidence->type] }}</dd>
                        <dt>Tanggal pencatatan</dt><dd>{{ $evidence->recorded_date?->format('d/m/Y') ?? '-' }}</dd>
                        @if ($evidence->type === 'fisik')
                            <dt>Lokasi penyimpanan</dt><dd>{{ $evidence->storage_location ?? '-' }}</dd>
                            <dt>Deskripsi</dt><dd class="text-pre-line">{{ $evidence->description ?: '-' }}</dd>
                            <dt>Foto</dt><dd>{{ $evidence->photo_path ? 'Ada (unduh lewat tombol di atas)' : 'Tidak ada foto' }}</dd>
                        @else
                            <dt>Nama berkas asli</dt><dd class="text-break">{{ $evidence->original_name }}</dd>
                            <dt>Format</dt><dd>{{ $evidence->format }}</dd>
                            <dt>Ukuran</dt><dd>{{ $evidence->sizeLabel() }}</dd>
                            <dt>Tanggal unggah</dt><dd>{{ $evidence->created_at->format('d/m/Y H:i') }}</dd>
                            <dt>Hash SHA-256 asli (pembanding)</dt><dd class="hash-value">{{ $evidence->sha256_hash }}</dd>
                            @if ($evidence->latestReupload)
                                <dt>Berkas saat ini</dt>
                                <dd class="text-break">{{ $evidence->latestReupload->original_name }} (unggah ulang {{ $evidence->latestReupload->checked_at->format('d/m/Y H:i') }})</dd>
                            @endif
                        @endif
                        <dt>Keterangan</dt><dd class="text-pre-line">{{ $evidence->notes ?: '-' }}</dd>
                        <dt>Dicatat oleh</dt><dd>{{ $evidence->uploader?->name ?? '-' }}</dd>
                    </dl>
                </div>
            </section>
        </div>

        @if ($evidence->type === 'digital')
            <div class="col-lg-5">
                <section class="card h-100" aria-labelledby="judul-integritas">
                    <div class="card-header"><h2 class="card-title" id="judul-integritas">Pemeriksaan integritas</h2></div>
                    <div class="card-body">
                        @if ($latestCheck)
                            <x-hash-result :check="$latestCheck" class="mb-3" />
                        @else
                            <p class="text-muted">
                                <i class="bi bi-shield" aria-hidden="true"></i>
                                Belum pernah diperiksa. Pemeriksaan menghitung ulang hash berkas yang tersimpan lalu membandingkannya dengan hash saat diunggah.
                            </p>
                        @endif

                        @can('checkHash', $evidence)
                            <form action="{{ route('evidence.hash-check', $evidence) }}" method="POST" data-uploading="Memeriksa...">
                                @csrf
                                <button type="submit" class="btn btn-accent">
                                    <i class="bi bi-shield-check" aria-hidden="true"></i> Periksa integritas sekarang
                                </button>
                            </form>

                            <hr class="my-3">
                            <form action="{{ route('evidence.reupload', $evidence) }}" method="POST" enctype="multipart/form-data" novalidate
                                  data-uploading="Mengunggah dan membandingkan hash...">
                                @csrf
                                <x-field name="file" label="Unggah ulang berkas" type="file" required accept=".pdf,.jpg,.jpeg,.png,.log,.txt,.pcap"
                                         help="Hash berkas baru dibandingkan dengan hash asli. Berkas lama tetap disimpan." />
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="bi bi-upload" aria-hidden="true"></i> Unggah ulang berkas
                                </button>
                            </form>
                        @endcan
                    </div>
                </section>
            </div>

            <div class="col-12">
                <section class="card">
                    <div class="card-header"><h2 class="card-title">Riwayat pemeriksaan</h2></div>
                    <div class="table-responsive">
                        <table class="table table-app mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Waktu</th>
                                    <th scope="col">Kegiatan</th>
                                    <th scope="col">Oleh</th>
                                    <th scope="col">Hasil</th>
                                    <th scope="col">Hash saat itu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($evidence->hashChecks as $check)
                                    <tr>
                                        <td class="text-nowrap">{{ $check->checked_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if ($check->action === 'unggah_ulang')
                                                Unggah ulang<br>
                                                <span class="small text-break">{{ $check->original_name }}</span>
                                                <a href="{{ $downloadUrl($check) }}" class="small d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-download" aria-hidden="true"></i> Unduh
                                                </a>
                                            @else
                                                Pemeriksaan
                                            @endif
                                        </td>
                                        <td>{{ $check->checker?->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge-status {{ $check->is_match ? 'badge-cocok' : 'badge-tidak-cocok' }}">
                                                {{ $check->is_match ? 'Cocok' : 'Tidak cocok' }}
                                            </span>
                                        </td>
                                        <td class="hash-value">{{ $check->computed_hash ?: 'Berkas tidak ditemukan' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="empty-state">
                                            <i class="bi bi-clock-history" aria-hidden="true"></i>
                                            Belum ada riwayat. Jalankan pemeriksaan integritas untuk mencatat yang pertama.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
    // Cegah klik ganda saat pemeriksaan berjalan
    document.querySelectorAll('form[data-uploading]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var button = form.querySelector('[type=submit]');
            button.disabled = true;
            button.textContent = form.dataset.uploading;
        });
    });
</script>
@endpush
