{{-- Kartu hasil pemeriksaan integritas SHA-256. Contoh: <x-hash-result :check="$evidence->hashChecks->first()" /> --}}
@props(['check'])

<div {{ $attributes->class(['hash-result', $check->is_match ? 'hash-match' : 'hash-mismatch']) }} role="status">
    <p class="hash-result-title">
        <i class="bi {{ $check->is_match ? 'bi-shield-check' : 'bi-shield-exclamation' }}" aria-hidden="true"></i>
        {{ $check->is_match ? 'Cocok' : 'Tidak cocok' }}
    </p>
    <p class="hash-result-meta">Diperiksa {{ $check->checked_at->format('d/m/Y H:i') }} oleh {{ $check->checker?->name ?? '-' }}</p>

    @if ($check->action === 'unggah_ulang')
        <p class="mb-2 text-break">Unggah ulang: {{ $check->original_name }}</p>
    @endif

    @if ($check->is_match)
        <p class="mb-0">Hash berkas saat ini sama dengan hash saat diunggah. Isi berkas tidak berubah.</p>
    @elseif ($check->computed_hash === '')
        <p class="mb-0">Berkas tidak ditemukan di penyimpanan. Laporkan ke admin sebelum bukti ini dipakai.</p>
    @else
        <p class="mb-0">Hash berkas saat ini berbeda dari hash saat diunggah. Isi berkas kemungkinan telah diubah atau rusak, jadi jangan dipakai sebagai bukti sebelum ditelusuri.</p>
    @endif

    @if (filled($check->computed_hash))
        <dl class="hash-compare">
            <dt>Hash saat diunggah</dt><dd class="hash-box hash-value">{{ $check->stored_hash }}</dd>
            <dt>Hash saat diperiksa</dt><dd class="hash-box hash-value mb-0">{{ $check->computed_hash }}</dd>
        </dl>
    @endif
</div>
