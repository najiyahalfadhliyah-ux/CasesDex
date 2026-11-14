{{-- Dipakai bersama oleh suspects.create dan suspects.edit --}}
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

<div class="alert alert-light border d-flex gap-2 small" role="note">
    <i class="bi bi-info-circle" aria-hidden="true"></i>
    <div>
        Gunakan data simulasi saja. Jangan isi alamat, nomor identitas, nomor telepon, atau data pribadi lain.
        Status hukum terpisah dari status penanganan kasus dan bukan kesimpulan bahwa seseorang bersalah.
    </div>
</div>

<div class="row g-0 gx-md-4">
    <div class="col-md-7">
        <x-field name="name" label="Nama atau nama samaran" :value="$suspect->name" required maxlength="255"
                 help="Contoh: inisial atau nama samaran." />
    </div>
    <div class="col-md-5">
        <x-select name="legal_status" label="Status hukum" :options="App\Models\Suspect::LEGAL_STATUSES" :value="$suspect->legal_status" required />
    </div>
    <div class="col-12">
        <x-textarea name="relation_description" label="Keterkaitan dengan kasus" :value="$suspect->relation_description"
                    required rows="4" maxlength="5000" />
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('error-summary')?.focus();
</script>
@endpush
