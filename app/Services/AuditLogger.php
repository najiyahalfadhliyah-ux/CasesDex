<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

// Satu-satunya pintu pencatatan audit log, supaya format seragam.
// Dipanggil oleh AuditObserver (perubahan data) dan AuthController (login/logout).
class AuditLogger
{
    // Field yang nilainya TIDAK PERNAH disimpan; cukup dicatat "diubah"
    private const SECRET = ['password', 'remember_token'];

    /**
     * @param  array<string, array{old: mixed, new: mixed}>|null  $changes
     * @param  User|null|false  $user  false = ambil pengguna yang login; null = "Sistem"
     */
    public function log(string $action, ?Model $subject = null, ?string $summary = null, ?array $changes = null, User|null|false $user = false): AuditLog
    {
        $user = $user === false ? auth()->user() : $user;

        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => $subject ? $subject::class : null,
            'auditable_id' => $subject?->getKey(),
            'summary' => $summary,
            'changes' => $changes ? $this->clean($changes) : null,
        ]);
    }

    // Sembunyikan rahasia dan potong teks panjang agar log tetap ringkas
    private function clean(array $changes): array
    {
        foreach ($changes as $field => $pair) {
            $changes[$field] = in_array($field, self::SECRET, true)
                ? ['old' => null, 'new' => '(diubah)']
                : array_map(fn ($v) => is_string($v) ? Str::limit($v, 200) : $v, $pair);
        }

        return $changes;
    }
}
