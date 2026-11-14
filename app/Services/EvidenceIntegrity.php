<?php

namespace App\Services;

use App\Models\Evidence;
use App\Models\HashCheck;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Integritas bukti digital. Pembanding selalu hash ASLI (evidence.sha256_hash), yang tidak pernah diubah.
// Semua catatan lewat Eloquent create supaya Observer audit log (Tahap 7) bisa mencatatnya.
class EvidenceIntegrity
{
    // Hitung ulang hash berkas yang sedang berlaku lalu catat hasilnya
    public function check(Evidence $evidence, User $user): HashCheck
    {
        $disk = Storage::disk('local');
        $path = $evidence->currentFilePath();

        // Berkas hilang dianggap tidak cocok; computed_hash kosong menandai "berkas tidak ditemukan"
        $computed = $disk->exists($path) ? hash_file('sha256', $disk->path($path)) : '';

        return $this->record($evidence, $user, $computed, ['action' => 'periksa', 'file_path' => $path]);
    }

    // Simpan berkas baru (berkas lama tetap ada) dan langsung bandingkan dengan hash asli
    public function reupload(Evidence $evidence, UploadedFile $file, User $user): HashCheck
    {
        $computed = hash_file('sha256', $file->getRealPath());

        return $this->record($evidence, $user, $computed, [
            'action' => 'unggah_ulang',
            'file_path' => $file->store('evidence', 'local'), // nama acak
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);
    }

    private function record(Evidence $evidence, User $user, string $computed, array $extra): HashCheck
    {
        return $evidence->hashChecks()->create($extra + [
            'checked_by' => $user->id,
            'computed_hash' => $computed,
            'stored_hash' => $evidence->sha256_hash,
            'is_match' => hash_equals($evidence->sha256_hash, $computed),
            'checked_at' => now(),
        ]);
    }
}
