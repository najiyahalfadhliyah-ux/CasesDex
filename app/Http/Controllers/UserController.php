<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Semua perubahan lewat Eloquent (create/update) supaya Observer audit log di Tahap 7 bisa menangkapnya
class UserController extends Controller
{
    public function index(): View
    {
        $users = User::withCount('assignedCases')->orderBy('name')->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create', ['user' => new User(['role' => 'petugas'])]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        // Password di-hash otomatis oleh cast 'hashed' di model User
        $user = User::create($request->validated() + ['is_active' => true]);

        return redirect()->route('users.index')->with('success', "Akun {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']); // kata sandi lama tetap dipakai
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', "Akun {$user->name} berhasil diperbarui.");
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        // Keluarkan sesi yang sedang berjalan agar akun nonaktif langsung tidak bisa dipakai
        if (! $user->is_active && config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }
}
