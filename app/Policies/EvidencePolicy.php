<?php

namespace App\Policies;

use App\Models\Evidence;
use App\Models\User;

// Hak akses bukti mengikuti kasusnya. Menambah bukti dicek lewat CaseRecordPolicy::update.
class EvidencePolicy
{
    // Dipakai juga untuk unduh berkas
    public function view(User $user, Evidence $evidence): bool
    {
        return $this->canAccessCase($user, $evidence);
    }

    public function update(User $user, Evidence $evidence): bool
    {
        return $evidence->is_active && $this->canAccessCase($user, $evidence);
    }

    public function checkHash(User $user, Evidence $evidence): bool
    {
        return $evidence->type === 'digital' && $this->update($user, $evidence);
    }

    // Nonaktifkan / aktifkan kembali: khusus admin, sama seperti kasus
    public function toggleActive(User $user, Evidence $evidence): bool
    {
        return $user->role === 'admin' && $evidence->case !== null;
    }

    private function canAccessCase(User $user, Evidence $evidence): bool
    {
        $case = $evidence->case; // null jika kasusnya sudah dinonaktifkan (soft delete)

        return $case !== null && ($user->role === 'admin' || $case->assigned_to === $user->id);
    }
}
