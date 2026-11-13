<?php

namespace App\Services;

use App\Models\Evidence;
use App\Models\HashCheck;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

// Pemeriksaan integritas: hitung ulang SHA-256 berkas tersimpan lalu catat hasilnya.
class EvidenceIntegrity
{
    public function check(Evidence $evidence, User $user): HashCheck
    {
        $disk = Storage::disk('local');

        // Berkas hilang dianggap tidak cocok; computed_hash kosong menandai "berkas tidak ditemukan"
        $computed = $disk->exists($evidence->file_path)
            ? hash_file('sha256', $disk->path($evidence->file_path))
            : '';

        // Lewat Eloquent create supaya Observer audit log (Tahap 7) bisa mencatatnya
        return $evidence->hashChecks()->create([
            'checked_by' => $user->id,
            'computed_hash' => $computed,
            'stored_hash' => $evidence->sha256_hash,
            'is_match' => hash_equals($evidence->sha256_hash, $computed),
            'checked_at' => now(),
        ]);
    }
}
