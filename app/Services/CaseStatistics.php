<?php

namespace App\Services;

use App\Models\CaseRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

// Angka dashboard dan laporan. Semua lewat query agregat (GROUP BY / SUM) di database, bukan menghitung di PHP,
// dan selalu berangkat dari visibleTo() supaya petugas hanya menghitung kasus miliknya.
class CaseStatistics
{
    // Kasus yang dihitung: sesuai hak akses, opsional dibatasi periode tanggal kejadian
    public function base(User $user, ?string $from = null, ?string $to = null): Builder
    {
        return CaseRecord::visibleTo($user)
            ->when($from, fn ($q) => $q->whereDate('incident_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('incident_date', '<=', $to));
    }

    /** @return array{summary: array<string, int>, by_status: array<string, int>, by_category: array<string, int>, by_year: array<int, int>} */
    public function summarize(Builder $base): array
    {
        // Satu query untuk keempat kartu
        $row = (clone $base)->toBase()->selectRaw(
            "count(*) as total,
             sum(case when status = 'dalam_penanganan' then 1 else 0 end) as dalam_penanganan,
             sum(case when status = 'selesai' then 1 else 0 end) as selesai,
             sum(case when archive_status = 'diarsipkan' then 1 else 0 end) as diarsipkan"
        )->first();

        return [
            'summary' => array_map('intval', (array) $row),
            // Status/kategori tanpa kasus tetap muncul dengan angka 0, urutan mengikuti konstanta
            'by_status' => array_merge(array_fill_keys(array_keys(CaseRecord::STATUSES), 0), $this->countBy($base, 'status')),
            'by_category' => array_merge(array_fill_keys(CaseRecord::CATEGORIES, 0), $this->countBy($base, 'category')),
            // substr tanggal: jalan di MySQL dan SQLite (tes), beda dengan YEAR()
            'by_year' => $this->countBy($base, 'substr(incident_date, 1, 4)'),
        ];
    }

    // Kasus aktif (belum diarsipkan) yang tenggat arsipnya <= $days hari lagi atau sudah lewat. Hanya informasi.
    public function nearArchiveDeadline(User $user, int $days = 30)
    {
        return CaseRecord::visibleTo($user)
            ->where('archive_status', 'aktif')
            ->whereDate('archive_deadline', '<=', today()->addDays($days))
            ->orderBy('archive_deadline')
            ->limit(10)
            ->get(['id', 'case_number', 'title', 'archive_deadline']);
    }

    /** @return array<string|int, int> */
    private function countBy(Builder $base, string $expression): array
    {
        return (clone $base)->toBase() // toBase tetap menerapkan soft delete: kasus nonaktif tidak dihitung
            ->selectRaw("{$expression} as label, count(*) as total") // $expression hanya dari kode ini, bukan input pengguna
            ->groupByRaw($expression)
            ->orderByRaw($expression)
            ->pluck('total', 'label')
            ->map(fn ($n) => (int) $n)
            ->all();
    }
}
