<?php

namespace App\Services;

use App\Models\CaseRecord;

class CaseNumberGenerator
{
    /**
     * Nomor kasus berikutnya: AK-TAHUN-0001, urutan mulai lagi dari 0001 tiap tahun.
     * Wajib dipanggil di dalam DB::transaction agar lockForUpdate menahan request lain
     * sampai kasus tersimpan. Index unique case_number tetap jadi pengaman terakhir.
     */
    public function next(): string
    {
        $prefix = 'AK-'.now()->year.'-';

        // withTrashed: nomor kasus yang sudah dinonaktifkan tidak dipakai ulang
        // shortcut: urut sebagai teks, benar sampai 9999 kasus per tahun
        $last = CaseRecord::withTrashed()
            ->where('case_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('case_number')
            ->value('case_number');

        $sequence = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        return $prefix.sprintf('%04d', $sequence);
    }
}
