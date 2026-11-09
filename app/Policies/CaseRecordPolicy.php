<?php

namespace App\Policies;

use App\Models\CaseRecord;
use App\Models\User;

// Ditemukan otomatis oleh Laravel karena namanya = nama model + "Policy"
class CaseRecordPolicy
{
    // Admin boleh semua aksi; method di bawah hanya berlaku untuk petugas
    public function before(User $user): ?bool
    {
        return $user->role === 'admin' ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true; // daftar disaring lewat scope visibleTo
    }

    public function view(User $user, CaseRecord $case): bool
    {
        return $case->assigned_to === $user->id;
    }

    public function update(User $user, CaseRecord $case): bool
    {
        return $case->assigned_to === $user->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, CaseRecord $case): bool
    {
        return false;
    }

    // Menugaskan atau memindahkan petugas
    public function assign(User $user): bool
    {
        return false;
    }
}
