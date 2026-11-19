{{-- Badge status kasus: ikon + teks. Contoh: <x-status-badge :status="$case->status" /> --}}
@props(['status'])

@php($icon = ['dalam_penanganan' => 'clock', 'selesai' => 'check2-circle', 'ditutup' => 'lock', 'ditangguhkan' => 'pause-circle'][$status] ?? 'circle')

<span {{ $attributes->class(['badge-status', "badge-{$status}"]) }}><i class="bi bi-{{ $icon }}" aria-hidden="true"></i>{{ \App\Models\CaseRecord::STATUSES[$status] ?? $status }}</span>
