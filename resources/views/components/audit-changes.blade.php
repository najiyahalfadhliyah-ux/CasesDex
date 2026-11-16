{{-- Ringkasan perubahan audit log: field, nilai lama (dicoret), nilai baru. Contoh: <x-audit-changes :changes="$log->changes" /> --}}
@props(['changes' => []])

@php
    // Nilai mentah -> teks tampilan
    $show = fn ($v) => match (true) {
        $v === null, $v === '' => '(kosong)',
        is_bool($v) => $v ? 'Ya' : 'Tidak',
        default => (string) $v,
    };
@endphp

<ul class="audit-changes">
    @foreach ($changes as $field => $pair)
        @php($mono = $field === 'sha256')
        <li>
            <span class="audit-field">{{ \App\Models\AuditLog::FIELDS[$field] ?? $field }}</span>
            <span class="audit-diff">
                <del class="{{ $mono ? 'hash-value' : '' }}"><span class="visually-hidden">sebelum: </span>{{ $show($pair['old'] ?? null) }}</del>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                <ins class="{{ $mono ? 'hash-value' : '' }}"><span class="visually-hidden">sesudah: </span>{{ $show($pair['new'] ?? null) }}</ins>
            </span>
        </li>
    @endforeach
</ul>
