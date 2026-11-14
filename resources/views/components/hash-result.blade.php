{{-- Hasil pemeriksaan integritas SHA-256. Contoh: <x-hash-result :check="$evidence->hashChecks->first()" /> --}}
@props(['check'])

<div {{ $attributes->class(['hash-result', $check->is_match ? 'hash-match' : 'hash-mismatch']) }} role="status">
    <i class="bi {{ $check->is_match ? 'bi-shield-check' : 'bi-shield-exclamation' }} hash-result-icon" aria-hidden="true"></i>
    <div class="flex-grow-1">
        <p class="hash-result-title">{{ $check->is_match ? 'Cocok' : 'Tidak cocok' }}</p>
        @if ($check->action === 'unggah_ulang')
            <p class="mb-1 text-break">Unggah ulang: {{ $check->original_name }}</p>
        @endif
        @if ($check->is_match)
            <p class="mb-2">Hash berkas saat ini sama dengan hash saat diunggah. Isi berkas tidak berubah.</p>
        @elseif ($check->computed_hash === '')
            <p class="mb-2">Berkas tidak ditemukan di penyimpanan. Laporkan ke admin sebelum bukti ini dipakai.</p>
        @else
            <p class="mb-2">Hash berkas saat ini berbeda dari hash saat diunggah. Isi berkas kemungkinan telah diubah atau rusak, jadi jangan dipakai sebagai bukti sebelum ditelusuri.</p>
            <dl class="hash-compare">
                <dt>Hash saat diunggah</dt><dd class="hash-value">{{ $check->stored_hash }}</dd>
                <dt>Hash saat diperiksa</dt><dd class="hash-value">{{ $check->computed_hash }}</dd>
            </dl>
        @endif
        <small>Diperiksa {{ $check->checked_at->format('d/m/Y H:i') }} oleh {{ $check->checker?->name ?? '-' }}</small>
    </div>
</div>
