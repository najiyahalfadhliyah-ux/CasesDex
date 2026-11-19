{{-- Logo asli dari public/images; versi terang/gelap ditukar otomatis oleh CSS sesuai tema.
     type: simbol | teks. alt kosong bila di sebelahnya sudah ada teks "CasesDex".
     Contoh: <x-logo type="simbol" height="44" /> --}}
@props(['type' => 'simbol', 'height' => 44, 'alt' => ''])

<img src="{{ asset("images/logo-{$type}-terang.png") }}" alt="{{ $alt }}" height="{{ $height }}" {{ $attributes->class(['logo-light']) }}>
<img src="{{ asset("images/logo-{$type}-gelap.png") }}" alt="{{ $alt }}" height="{{ $height }}" {{ $attributes->class(['logo-dark']) }}>
