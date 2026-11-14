<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReuploadEvidenceRequest;
use App\Http\Requests\StoreEvidenceRequest;
use App\Http\Requests\UpdateEvidenceRequest;
use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\HashCheck;
use App\Services\EvidenceIntegrity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Semua perubahan lewat Eloquent (create/update) supaya Observer audit log di Tahap 7 bisa menangkapnya.
// Berkas disimpan di disk "local" = storage/app/private (tidak bisa dibuka lewat URL langsung).
class EvidenceController extends Controller
{
    public function create(Request $request, CaseRecord $case): View
    {
        Gate::authorize('update', $case);

        $type = $request->query('type') === 'digital' ? 'digital' : 'fisik';

        return view('evidence.create', [
            'case' => $case,
            'evidence' => new Evidence(['type' => $type, 'recorded_date' => today()]),
        ]);
    }

    public function store(StoreEvidenceRequest $request, CaseRecord $case): RedirectResponse
    {
        $data = $request->safe()->except(['photo', 'file']) + ['uploaded_by' => $request->user()->id];

        if ($data['type'] === 'digital') {
            $file = $request->file('file');
            $data = array_merge($data, [
                'name' => ($data['name'] ?? null) ?: $file->getClientOriginalName(),
                'original_name' => $file->getClientOriginalName(), // nama asli hanya metadata
                'format' => strtoupper($file->getClientOriginalExtension()),
                'size' => $file->getSize(),
                'sha256_hash' => hash_file('sha256', $file->getRealPath()), // dihitung dari berkas yang benar-benar diterima server
                'file_path' => $file->store('evidence', 'local'), // nama acak (hashName)
                'storage_location' => null,
            ]);
        } elseif ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('evidence/photos', 'local');
        }

        $evidence = $case->evidence()->create($data);

        return redirect()->route('evidence.show', $evidence)->with('success', "Bukti \"{$evidence->name}\" berhasil ditambahkan.");
    }

    public function show(Evidence $evidence): View
    {
        Gate::authorize('view', $evidence);

        $evidence->load('case', 'uploader', 'hashChecks.checker', 'latestReupload');

        return view('evidence.show', compact('evidence'));
    }

    public function edit(Evidence $evidence): View
    {
        Gate::authorize('update', $evidence);

        return view('evidence.edit', ['evidence' => $evidence, 'case' => $evidence->case]);
    }

    public function update(UpdateEvidenceRequest $request, Evidence $evidence): RedirectResponse
    {
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            // shortcut: foto lama tidak dihapus dari storage (jejak dokumentasi), bersihkan manual jika perlu
            $data['photo_path'] = $request->file('photo')->store('evidence/photos', 'local');
        }

        $evidence->update($data);

        return redirect()->route('evidence.show', $evidence)->with('success', "Bukti \"{$evidence->name}\" berhasil diperbarui.");
    }

    // Unduh berkas asli, versi unggah ulang ($hashCheck), atau foto bukti fisik.
    // Link wajib bertanda tangan + belum kedaluwarsa (middleware signed), lalu tetap dicek Policy.
    public function download(Evidence $evidence, ?HashCheck $hashCheck = null): StreamedResponse
    {
        Gate::authorize('view', $evidence);

        if ($hashCheck) {
            abort_unless($hashCheck->action === 'unggah_ulang', 404);
            [$path, $name] = [$hashCheck->file_path, $hashCheck->original_name];
        } elseif ($evidence->type === 'digital') {
            [$path, $name] = [$evidence->file_path, $evidence->original_name];
        } else {
            $path = $evidence->photo_path;
            $name = "foto-bukti-{$evidence->id}.".pathinfo((string) $path, PATHINFO_EXTENSION);
        }

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $name);
    }

    public function reupload(ReuploadEvidenceRequest $request, Evidence $evidence, EvidenceIntegrity $integrity): RedirectResponse
    {
        $integrity->reupload($evidence, $request->file('file'), $request->user());

        // Hasil pembandingan dengan hash asli langsung tampil di halaman detail
        return redirect()->route('evidence.show', $evidence);
    }

    public function checkHash(Request $request, Evidence $evidence, EvidenceIntegrity $integrity): RedirectResponse
    {
        Gate::authorize('checkHash', $evidence);

        $integrity->check($evidence, $request->user());

        // Hasil terbaru langsung tampil di halaman detail bukti
        return redirect()->route('evidence.show', $evidence);
    }

    public function toggleActive(Evidence $evidence): RedirectResponse
    {
        Gate::authorize('toggleActive', $evidence);

        $evidence->update(['is_active' => ! $evidence->is_active]);

        $status = $evidence->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->route('evidence.show', $evidence)->with('success', "Bukti \"{$evidence->name}\" berhasil {$status}.");
    }
}
