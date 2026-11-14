<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimelineEventRequest;
use App\Http\Requests\UpdateTimelineEventRequest;
use App\Models\CaseRecord;
use App\Models\TimelineEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// Halaman kronologi per kasus. Semua perubahan lewat Eloquent supaya Observer audit log (Tahap 7) bisa menangkapnya.
class TimelineEventController extends Controller
{
    public function index(CaseRecord $case): View
    {
        Gate::authorize('view', $case);

        // Relasi timelineEvents sudah urut menurut event_time (waktu kejadian), bukan waktu input
        $case->load('timelineEvents.evidence', 'timelineEvents.recorder');

        return view('timeline.index', compact('case'));
    }

    public function create(CaseRecord $case): View
    {
        Gate::authorize('update', $case);

        return view('timeline.create', [
            'case' => $case,
            'event' => new TimelineEvent,
            'evidenceOptions' => $this->evidenceOptions($case),
        ]);
    }

    public function store(StoreTimelineEventRequest $request, CaseRecord $case): RedirectResponse
    {
        $event = $case->timelineEvents()->create($request->validated() + ['recorded_by' => $request->user()->id]);

        return $this->backToTimeline($case, "Peristiwa \"{$event->title}\" berhasil ditambahkan.");
    }

    public function edit(TimelineEvent $timelineEvent): View
    {
        Gate::authorize('update', $timelineEvent);

        return view('timeline.edit', [
            'case' => $timelineEvent->case,
            'event' => $timelineEvent,
            'evidenceOptions' => $this->evidenceOptions($timelineEvent->case),
        ]);
    }

    public function update(UpdateTimelineEventRequest $request, TimelineEvent $timelineEvent): RedirectResponse
    {
        $timelineEvent->update($request->validated()); // recorded_by tetap pencatat awal

        return $this->backToTimeline($timelineEvent->case, "Peristiwa \"{$timelineEvent->title}\" berhasil diperbarui.");
    }

    public function toggleActive(TimelineEvent $timelineEvent): RedirectResponse
    {
        Gate::authorize('toggleActive', $timelineEvent);

        $timelineEvent->update(['is_active' => ! $timelineEvent->is_active]);
        $status = $timelineEvent->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return $this->backToTimeline($timelineEvent->case, "Peristiwa \"{$timelineEvent->title}\" berhasil {$status}.");
    }

    // Pilihan bukti untuk dropdown: hanya bukti kasus ini
    private function evidenceOptions(CaseRecord $case)
    {
        return $case->evidence()->orderBy('name')->pluck('name', 'id');
    }

    private function backToTimeline(CaseRecord $case, string $message): RedirectResponse
    {
        return redirect()->route('cases.timeline-events.index', $case)->with('success', $message);
    }
}
