{{-- Dipakai bersama oleh timeline.create dan timeline.edit --}}
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
    <div class="col-md-5">
        <x-field name="event_time" label="Tanggal dan waktu kejadian" type="datetime-local"
                 :value="$event->event_time?->format('Y-m-d\TH:i')" required
                 help="Timeline diurutkan menurut waktu ini." />
    </div>
    <div class="col-md-7">
        <x-field name="title" label="Judul peristiwa" :value="$event->title" required maxlength="255" />
    </div>
    <div class="col-12">
        <x-textarea name="description" label="Deskripsi rinci" :value="$event->description" required rows="5" maxlength="10000" />
        <x-select name="evidence_id" label="Barang bukti terkait" :options="$evidenceOptions" :value="$event->evidence_id"
                  placeholder="Tidak ada" help="Opsional. Hanya bukti dari kasus ini." />
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('error-summary')?.focus();
</script>
@endpush
