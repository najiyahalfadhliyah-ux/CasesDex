<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

// Satu policy untuk data milik kasus (Suspect, TimelineEvent), dipasang lewat #[UsePolicy] di model.
// Aturannya ikut CaseRecordPolicy::update: admin semua kasus, petugas hanya kasus yang ditugaskan.
// Menambah data dicek langsung ke kasusnya (can('update', $case)) di Form Request.
class CaseItemPolicy
{
    public function update(User $user, Model $item): bool
    {
        return $item->is_active && $this->toggleActive($user, $item);
    }

    public function toggleActive(User $user, Model $item): bool
    {
        $case = $item->case; // null jika kasusnya sudah dinonaktifkan (soft delete)

        return $case !== null && $user->can('update', $case);
    }
}
