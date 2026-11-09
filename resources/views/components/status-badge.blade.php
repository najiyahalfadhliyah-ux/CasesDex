{{-- Badge status kasus. Contoh: <x-status-badge :status="$case->status" /> --}}
@props(['status'])

<span {{ $attributes->class(['badge-status', "badge-{$status}"]) }}>{{ \App\Models\CaseRecord::STATUSES[$status] ?? $status }}</span>
