{{-- Tabel jumlah + persentase. Alternatif grafik (dashboard) dan isi laporan.
     Contoh: <x-stat-table :rows="$stats['by_status']" label="Status" :labels="CaseRecord::STATUSES" caption="Kasus per status" /> --}}
@props(['rows' => [], 'label', 'labels' => [], 'caption' => null])

@php($total = array_sum($rows))

<table {{ $attributes->class(['table', 'table-app', 'mb-0']) }}>
    @if ($caption)
        <caption class="visually-hidden">{{ $caption }}</caption>
    @endif
    <thead>
        <tr>
            <th scope="col">{{ $label }}</th>
            <th scope="col" class="text-end">Jumlah</th>
            <th scope="col" class="text-end">Persentase</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $key => $count)
            <tr>
                <th scope="row" class="fw-normal">{{ $labels[$key] ?? $key }}</th>
                <td class="text-end tabular-nums">{{ $count }}</td>
                <td class="text-end tabular-nums">{{ $total ? number_format($count / $total * 100, 1, ',', '.') : '0,0' }}%</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="empty-state">
                    <i class="bi bi-inbox" aria-hidden="true"></i>
                    Tidak ada kasus pada periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>
    @if ($rows)
        <tfoot>
            <tr>
                <th scope="row">Total</th>
                <td class="text-end tabular-nums fw-semibold">{{ $total }}</td>
                <td class="text-end tabular-nums">{{ $total ? '100%' : '0,0%' }}</td>
            </tr>
        </tfoot>
    @endif
</table>
