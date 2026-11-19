{{-- Tab navigasi (mis. detail kasus: Informasi, Barang bukti, ...). Tiap item: label, href, active, icon (opsional).
     Contoh: <x-tabs label="Bagian detail kasus" :items="[
                 ['label' => 'Informasi', 'href' => '#informasi', 'active' => true],
                 ['label' => 'Barang bukti', 'href' => '#bukti'],
             ]" /> --}}
@props(['items' => [], 'label'])

<nav {{ $attributes->class(['tabs-app']) }} aria-label="{{ $label }}">
    @foreach ($items as $item)
        <a href="{{ $item['href'] }}" @class(['tab-link', 'active' => $item['active'] ?? false])
           @if ($item['active'] ?? false) aria-current="page" @endif>
            @isset($item['icon'])<i class="bi bi-{{ $item['icon'] }}" aria-hidden="true"></i>@endisset
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
