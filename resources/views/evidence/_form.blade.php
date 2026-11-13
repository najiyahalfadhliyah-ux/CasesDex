{{-- Dipakai bersama oleh evidence.create dan evidence.edit. Field berbeda sesuai jenis bukti. --}}
@php($maxKb = config('evidence.max_kb'))

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

<div class="row g-0 gx-md-4">
    <div class="col-md-7">
        @if ($evidence->type === 'digital' && ! $evidence->exists)
            <x-field name="name" label="Nama bukti" :value="$evidence->name" maxlength="255"
                     help="Boleh dikosongkan, nanti memakai nama berkas." />
        @else
            <x-field name="name" label="Nama bukti" :value="$evidence->name" required maxlength="255" />
        @endif
    </div>
    <div class="col-md-5">
        <x-field name="recorded_date" label="Tanggal pencatatan" type="date" :value="$evidence->recorded_date?->format('Y-m-d')"
                 required max="{{ today()->format('Y-m-d') }}" />
    </div>

    @if ($evidence->type === 'fisik')
        <div class="col-12">
            <x-field name="storage_location" label="Lokasi penyimpanan" :value="$evidence->storage_location" required maxlength="255"
                     help="Contoh: Lemari arsip B, rak 2." />
            <x-textarea name="description" label="Deskripsi" :value="$evidence->description" rows="3" maxlength="5000" />
            <x-field name="photo" label="Foto bukti" type="file" accept=".jpg,.jpeg,.png"
                     help="{{ $evidence->photo_path ? 'Kosongkan jika foto tidak diganti. ' : 'Opsional. ' }}JPG atau PNG, maksimal {{ number_format($maxKb, 0, ',', '.') }} KB." />
        </div>
    @elseif (! $evidence->exists)
        <div class="col-12">
            <x-field name="file" label="Berkas bukti" type="file" required accept=".pdf,.jpg,.jpeg,.png,.log,.txt,.pcap"
                     help="PDF, JPG, PNG, LOG/TXT, atau PCAP, maksimal {{ number_format($maxKb, 0, ',', '.') }} KB. Hash SHA-256 dihitung otomatis oleh server." />
        </div>
    @else
        <div class="col-12">
            <p class="form-text mt-0 mb-3">
                <i class="bi bi-lock" aria-hidden="true"></i>
                Berkas digital tidak bisa diganti agar hash saat diunggah tetap menjadi acuan pemeriksaan integritas.
            </p>
        </div>
    @endif

    <div class="col-12">
        <x-textarea name="notes" label="Keterangan" :value="$evidence->notes" rows="3" maxlength="5000" />
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('error-summary')?.focus();

    // Cegah kirim ganda saat berkas sedang diunggah
    document.querySelectorAll('form[data-uploading]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var button = form.querySelector('[type=submit]');
            button.disabled = true;
            button.textContent = form.dataset.uploading;
        });
    });
</script>
@endpush
