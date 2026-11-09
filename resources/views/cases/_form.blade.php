{{-- Dipakai bersama oleh cases.create dan cases.edit --}}
@php($statuses = App\Models\CaseRecord::STATUSES)
@php($categories = array_combine(App\Models\CaseRecord::CATEGORIES, App\Models\CaseRecord::CATEGORIES))

@if ($errors->any())
    <div class="alert alert-danger" role="alert" tabindex="-1" id="error-summary">
        <strong>Periksa kembali isian berikut:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->keys() as $field)
                <li><a href="#{{ $field }}" class="alert-link">{{ $errors->first($field) }}</a></li>
            @endforeach
        </ul>
    </div>
@endif

<h2 class="h6 fw-semibold mb-3">Identitas kasus</h2>
<div class="row g-0 gx-md-4">
    <div class="col-12">
        <x-field name="title" label="Judul kasus" :value="$case->title" required maxlength="255" />
    </div>
    <div class="col-md-6">
        <x-select name="category" label="Kategori" :options="$categories" :value="$case->category" placeholder="Pilih kategori" required />
    </div>
    <div class="col-md-6">
        <x-field name="location" label="Lokasi kejadian" :value="$case->location" required maxlength="255" />
    </div>
    <div class="col-md-6">
        <x-field name="incident_date" label="Tanggal kejadian" type="date" :value="$case->incident_date?->format('Y-m-d')"
                 required max="{{ today()->format('Y-m-d') }}" />
    </div>
    <div class="col-md-6">
        <x-field name="investigation_start_date" label="Tanggal mulai investigasi" type="date"
                 :value="$case->investigation_start_date?->format('Y-m-d')" required />
    </div>
    <div class="col-12">
        <x-textarea name="description" label="Deskripsi singkat" :value="$case->description" required rows="3" maxlength="5000" />
    </div>
</div>

<hr class="my-3">
<h2 class="h6 fw-semibold mb-3">Penanganan</h2>
<div class="row g-0 gx-md-4">
    <div class="col-md-6">
        <x-select name="status" label="Status" :options="$statuses" :value="$case->status" required />
    </div>
    <div class="col-md-6">
        <x-field name="completed_date" label="Tanggal selesai" type="date" :value="$case->completed_date?->format('Y-m-d')"
                 help="Wajib diisi jika status Selesai." />
    </div>

    @can('assign', $case->exists ? $case : App\Models\CaseRecord::class)
        <div class="col-md-6">
            <x-select name="assigned_to" label="Petugas yang ditugaskan" :options="$officers" :value="$case->assigned_to"
                      placeholder="Belum ditugaskan" help="Hanya petugas aktif yang bisa dipilih." />
        </div>
        @if ($case->exists)
            <div class="col-md-6">
                <x-field name="archive_deadline" label="Tenggat arsip" type="date" :value="$case->archive_deadline?->format('Y-m-d')" required />
            </div>
        @endif
    @endcan
</div>

<hr class="my-3">
<h2 class="h6 fw-semibold mb-1">Analisis</h2>
<p class="text-muted small mb-3">Boleh dikosongkan dan diisi nanti.</p>
<x-textarea name="summary" label="Ringkasan kasus" :value="$case->summary" rows="4" maxlength="10000" />
<x-textarea name="suspected_motive" label="Dugaan motif" :value="$case->suspected_motive" rows="3" maxlength="5000" />

@push('scripts')
<script>
    // Arahkan fokus ke ringkasan error setelah validasi gagal (untuk pengguna keyboard/pembaca layar)
    document.getElementById('error-summary')?.focus();
</script>
@endpush
