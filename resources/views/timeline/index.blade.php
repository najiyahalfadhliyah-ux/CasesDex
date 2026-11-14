@extends('layouts.app')

@section('title', "Kronologi {$case->case_number} - CasesDex")
@section('heading', 'Kronologi')

@section('page')
    <a href="{{ route('cases.show', $case) }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small mb-3">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Kasus {{ $case->case_number }}
    </a>

    <x-flash />

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <p class="case-number fs-5 mb-1">{{ $case->case_number }}</p>
            <h1 class="h3 fw-bold mb-2">Kronologi: {{ $case->title }}</h1>
            <x-status-badge :status="$case->status" />
        </div>
        @can('update', $case)
            <a href="{{ route('cases.timeline-events.create', $case) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah peristiwa
            </a>
        @endcan
    </div>

    {{-- Ringkasan dan dugaan motif disimpan per kasus (tabel cases), bukan per peristiwa --}}
    <div class="row g-3 mb-3">
        @foreach (['summary' => ['Ringkasan kasus', 10000, 'bi-journal-text'], 'suspected_motive' => ['Dugaan motif', 5000, 'bi-lightbulb']] as $field => [$label, $max, $icon])
            <div class="col-lg-6">
                <section class="card h-100" aria-labelledby="judul-{{ $field }}">
                    <div class="card-header d-flex align-items-center justify-content-between gap-2">
                        <h2 class="card-title d-flex align-items-center gap-2" id="judul-{{ $field }}">
                            <i class="bi {{ $icon }}" aria-hidden="true"></i> {{ $label }}
                        </h2>
                        @can('update', $case)
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#form-{{ $field }}" aria-controls="form-{{ $field }}"
                                    aria-expanded="{{ $errors->has($field) ? 'true' : 'false' }}">
                                <i class="bi bi-pencil" aria-hidden="true"></i> Ubah
                            </button>
                        @endcan
                    </div>
                    <div class="card-body">
                        <p class="text-pre-line mb-0 {{ $case->$field ? '' : 'text-muted' }}">{{ $case->$field ?: 'Belum diisi.' }}</p>

                        @can('update', $case)
                            <form action="{{ route('cases.analysis', $case) }}" method="POST" novalidate
                                  id="form-{{ $field }}" class="collapse mt-3 {{ $errors->has($field) ? 'show' : '' }}">
                                @csrf
                                @method('PATCH')
                                <x-textarea :name="$field" :label="$label" :value="$case->$field" rows="5" :maxlength="$max" />
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-check2" aria-hidden="true"></i> Simpan {{ strtolower($label) }}
                                </button>
                            </form>
                        @endcan
                    </div>
                </section>
            </div>
        @endforeach
    </div>

    <section class="card" aria-labelledby="judul-timeline">
        <div class="card-header">
            <h2 class="card-title" id="judul-timeline">Timeline peristiwa ({{ $case->timelineEvents->count() }})</h2>
            <p class="text-muted small mb-0 mt-1">Diurutkan menurut waktu kejadian, bukan waktu pencatatan.</p>
        </div>
        <div class="card-body">
            @if ($case->timelineEvents->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-clock-history" aria-hidden="true"></i>
                    Belum ada peristiwa. Catat peristiwa pertama dengan tombol "Tambah peristiwa".
                </div>
            @else
                <ol class="timeline">
                    @foreach ($case->timelineEvents as $event)
                        <li @class(['timeline-item', 'is-inactive' => ! $event->is_active])>
                            <time class="timeline-time" datetime="{{ $event->event_time->format('Y-m-d\TH:i') }}">
                                {{ $event->event_time->format('d/m/Y H:i') }}
                            </time>
                            <div class="timeline-body">
                                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                    <h3 class="text-break">{{ $event->title }}</h3>
                                    @unless ($event->is_active)
                                        <span class="badge-status badge-nonaktif">Nonaktif</span>
                                    @endunless
                                </div>
                                <p class="text-pre-line mb-2">{{ $event->description }}</p>
                                <p class="timeline-meta mb-0">
                                    @if ($event->evidence)
                                        <i class="bi bi-box-seam" aria-hidden="true"></i>
                                        Bukti terkait: <a href="{{ route('evidence.show', $event->evidence) }}">{{ $event->evidence->name }}</a> &middot;
                                    @endif
                                    Dicatat oleh {{ $event->recorder?->name ?? '-' }}
                                </p>

                                @canany(['update', 'toggleActive'], $event)
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        @can('update', $event)
                                            <a href="{{ route('timeline-events.edit', $event) }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-pencil" aria-hidden="true"></i> Ubah
                                            </a>
                                        @endcan
                                        @can('toggleActive', $event)
                                            <form action="{{ route('timeline-events.toggle-active', $event) }}" method="POST"
                                                  data-confirm="{{ $event->is_active ? "Nonaktifkan peristiwa {$event->title}? Data tetap tersimpan dan bisa diaktifkan kembali." : "Aktifkan kembali peristiwa {$event->title}?" }}">
                                                @csrf
                                                @method('PATCH')
                                                @if ($event->is_active)
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="bi bi-slash-circle" aria-hidden="true"></i> Nonaktifkan
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-outline-success btn-sm">
                                                        <i class="bi bi-check-circle" aria-hidden="true"></i> Aktifkan
                                                    </button>
                                                @endif
                                            </form>
                                        @endcan
                                    </div>
                                @endcanany
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
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
