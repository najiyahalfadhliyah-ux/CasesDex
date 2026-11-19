{{-- Kartu dengan judul opsional dan slot aksi di kanan judul. :body="false" untuk isi tanpa padding (mis. tabel).
     Contoh: <x-card title="Kasus terbaru">...</x-card>
             <x-card title="Daftar kasus" :body="false"><x-slot:actions>...</x-slot:actions><x-table>...</x-table></x-card> --}}
@props(['title' => null, 'body' => true])

<section {{ $attributes->class(['card']) }}>
    @if ($title || isset($actions))
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            @if ($title)<h2 class="card-title">{{ $title }}</h2>@endif
            {{ $actions ?? '' }}
        </div>
    @endif
    @if ($body)
        <div class="card-body">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif
</section>
