{{-- Tombol. variant: primary | secondary | ghost | danger | outline-danger. Dengan href jadi <a>, tanpa href jadi <button>.
     Contoh: <x-button icon="plus-lg">Tambah kasus</x-button>
             <x-button variant="secondary" :href="route('cases.edit', $case)">Ubah</x-button>
             Tombol ikon saja wajib aria-label: <x-button variant="ghost" icon="x-lg" aria-label="Tutup" /> --}}
@props(['variant' => 'primary', 'href' => null, 'icon' => null, 'size' => null])

@php($classes = ['btn', "btn-{$variant}", "btn-{$size}" => $size])

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<i class="bi bi-{{ $icon }}" aria-hidden="true"></i>@endif{{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit'])->class($classes) }}>
        @if ($icon)<i class="bi bi-{{ $icon }}" aria-hidden="true"></i>@endif{{ $slot }}
    </button>
@endif
