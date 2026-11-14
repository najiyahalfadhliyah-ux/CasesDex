<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UpdateCaseAnalysisRequest;
use App\Http\Requests\UpdateCaseRequest;
use App\Http\Requests\UpdateCaseStatusRequest;
use App\Models\CaseRecord;
use App\Models\User;
use App\Services\CaseNumberGenerator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// Semua perubahan lewat Eloquent (create/update/delete) supaya Observer audit log di Tahap 7 bisa menangkapnya
class CaseController extends Controller
{
    public function index(Request $request): View
    {
        $cases = CaseRecord::visibleTo($request->user())
            ->with('officer')
            // Nomor terbesar di atas. Format AK-TAHUN-0001 selalu sama panjang, jadi urut teks = urut nomor
            ->orderByDesc('case_number')
            ->paginate(15);

        return view('cases.index', compact('cases'));
    }

    public function show(CaseRecord $case): View
    {
        Gate::authorize('view', $case);

        $case->load(['officer', 'creator', 'evidence' => fn ($q) => $q->latest(), 'suspects']);

        return view('cases.show', compact('case'));
    }

    public function create(): View
    {
        Gate::authorize('create', CaseRecord::class);

        return view('cases.create', [
            'case' => new CaseRecord(['status' => 'dalam_penanganan']),
            'officers' => $this->officerOptions(),
        ]);
    }

    public function store(StoreCaseRequest $request, CaseNumberGenerator $numbers): RedirectResponse
    {
        $data = $request->validated() + [
            'recorded_date' => today(),
            'archive_deadline' => Carbon::parse($request->validated('investigation_start_date'))->addMonths(6),
            'created_by' => $request->user()->id,
        ];

        // Nomor dibuat dan disimpan dalam satu transaksi; coba ulang jika bentrok nomor (duplikat)
        $case = retry(3, fn () => DB::transaction(
            fn () => CaseRecord::create($data + ['case_number' => $numbers->next()])
        ), 0, fn ($e) => $e instanceof UniqueConstraintViolationException);

        return redirect()->route('cases.show', $case)->with('success', "Kasus {$case->case_number} berhasil ditambahkan.");
    }

    public function edit(CaseRecord $case): View
    {
        Gate::authorize('update', $case);

        return view('cases.edit', [
            'case' => $case,
            'officers' => $this->officerOptions($case->assigned_to),
        ]);
    }

    public function update(UpdateCaseRequest $request, CaseRecord $case): RedirectResponse
    {
        $case->update($request->validated());

        return redirect()->route('cases.show', $case)->with('success', "Kasus {$case->case_number} berhasil diperbarui.");
    }

    // Ubah status cepat dari halaman detail
    public function updateStatus(UpdateCaseStatusRequest $request, CaseRecord $case): RedirectResponse
    {
        $case->update($request->validated());

        return redirect()->route('cases.show', $case)->with('success', "Status kasus {$case->case_number} berhasil diubah.");
    }

    // Ringkasan kasus / dugaan motif dari halaman kronologi (disimpan di tabel cases)
    public function updateAnalysis(UpdateCaseAnalysisRequest $request, CaseRecord $case): RedirectResponse
    {
        $case->update($request->validated());

        return redirect()->route('cases.timeline-events.index', $case)->with('success', 'Ringkasan dan dugaan motif berhasil disimpan.');
    }

    public function destroy(CaseRecord $case): RedirectResponse
    {
        Gate::authorize('delete', $case);

        $case->delete(); // soft delete: baris tetap ada, deleted_at terisi

        return redirect()->route('cases.index')->with('success', "Kasus {$case->case_number} berhasil dinonaktifkan.");
    }

    // Pilihan petugas untuk dropdown: petugas aktif + petugas yang sedang ditugaskan
    private function officerOptions(?int $currentOfficerId = null)
    {
        if (Gate::denies('assign', CaseRecord::class)) {
            return collect();
        }

        return User::where('role', 'petugas')
            ->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $currentOfficerId))
            ->orderBy('name')
            ->pluck('name', 'id');
    }
}
