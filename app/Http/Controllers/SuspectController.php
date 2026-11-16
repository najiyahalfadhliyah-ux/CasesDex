<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuspectRequest;
use App\Http\Requests\UpdateSuspectRequest;
use App\Models\CaseRecord;
use App\Models\Suspect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// Pihak terkait tampil di halaman detail kasus; controller ini hanya form tambah/ubah dan nonaktifkan.
// Semua perubahan lewat Eloquent (create/update) supaya AuditObserver mencatatnya; *OrFail / DB::transaction agar data + audit log satu transaksi.
class SuspectController extends Controller
{
    public function create(CaseRecord $case): View
    {
        Gate::authorize('update', $case);

        return view('suspects.create', ['case' => $case, 'suspect' => new Suspect(['legal_status' => 'dugaan'])]);
    }

    public function store(StoreSuspectRequest $request, CaseRecord $case): RedirectResponse
    {
        $suspect = DB::transaction(fn () => $case->suspects()->create($request->validated()));

        return $this->backToCase($case, "Pihak terkait \"{$suspect->name}\" berhasil ditambahkan.");
    }

    public function edit(Suspect $suspect): View
    {
        Gate::authorize('update', $suspect);

        return view('suspects.edit', ['case' => $suspect->case, 'suspect' => $suspect]);
    }

    public function update(UpdateSuspectRequest $request, Suspect $suspect): RedirectResponse
    {
        $suspect->updateOrFail($request->validated());

        return $this->backToCase($suspect->case, "Pihak terkait \"{$suspect->name}\" berhasil diperbarui.");
    }

    public function toggleActive(Suspect $suspect): RedirectResponse
    {
        Gate::authorize('toggleActive', $suspect);

        $suspect->updateOrFail(['is_active' => ! $suspect->is_active]);
        $status = $suspect->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return $this->backToCase($suspect->case, "Pihak terkait \"{$suspect->name}\" berhasil {$status}.");
    }

    private function backToCase(CaseRecord $case, string $message): RedirectResponse
    {
        return redirect()->to(route('cases.show', $case).'#pihak-terkait')->with('success', $message);
    }
}
