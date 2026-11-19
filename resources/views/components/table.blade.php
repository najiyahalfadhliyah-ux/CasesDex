{{-- Tabel data responsif. Biasanya di dalam <x-card :body="false">. Baris kosong pakai <td colspan class="empty-state">.
     Contoh: <x-table caption="Daftar kasus">
                 <x-slot:head><tr><th scope="col">Nomor kasus</th>...</tr></x-slot:head>
                 @foreach ($cases as $case) <tr>...</tr> @endforeach
             </x-table> --}}
@props(['caption' => null])

<div class="table-responsive">
    <table {{ $attributes->class(['table', 'table-app']) }}>
        @if ($caption)
            <caption class="visually-hidden">{{ $caption }}</caption>
        @endif
        <thead>{{ $head }}</thead>
        <tbody>{{ $slot }}</tbody>
    </table>
</div>
