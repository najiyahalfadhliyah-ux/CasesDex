<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArchiveCaseRequest;
use App\Models\CaseRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// Menu Arsip Kasus. Arsip manual oleh admin; batas 6 bulan (archive_deadline) hanya informasi.
// Perubahan lewat updateOrFail supaya AuditObserver mencatat case.archived / case.unarchived dalam satu transaksi.
class ArchiveController extends Controller
{
    public function index(Request $request): View
    {
        $cases = CaseRecord::visibleTo($request->user())
            ->where('archive_status', 'diarsipkan')
            ->with('officer')
            ->orderByDesc('archived_at')->orderByDesc('case_number')
            ->paginate(15);

        return view('archives.index', compact('cases'));
    }

    public function store(ArchiveCaseRequest $request, CaseRecord $case): RedirectResponse
    {
        $case->updateOrFail([
            'archive_status' => 'diarsipkan',
            'archived_at' => today(),
            'archive_reason' => $request->validated('archive_reason'),
        ]);

        return redirect()->route('cases.show', $case)->with('success', "Kasus {$case->case_number} berhasil diarsipkan.");
    }

    public function destroy(CaseRecord $case): RedirectResponse
    {
        Gate::authorize('archive', $case);

        $case->updateOrFail(['archive_status' => 'aktif', 'archived_at' => null, 'archive_reason' => null]);

        return redirect()->route('cases.show', $case)->with('success', "Arsip kasus {$case->case_number} berhasil dibuka kembali.");
    }
}
