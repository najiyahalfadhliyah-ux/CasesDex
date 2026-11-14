{{-- Status hukum pihak terkait. Sengaja netral (tanpa merah/hijau) agar tidak terkesan menyimpulkan bersalah.
     Contoh: <x-legal-status :status="$suspect->legal_status" /> --}}
@props(['status'])

<span {{ $attributes->class(['badge-legal']) }}>
    <i class="bi bi-bookmark" aria-hidden="true"></i>
    {{ \App\Models\Suspect::LEGAL_STATUSES[$status] ?? $status }}
</span>
