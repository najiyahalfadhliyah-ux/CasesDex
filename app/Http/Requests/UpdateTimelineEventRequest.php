<?php

namespace App\Http\Requests;

use App\Models\CaseRecord;

// Aturan, pesan, dan nama field sama dengan StoreTimelineEventRequest
class UpdateTimelineEventRequest extends StoreTimelineEventRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('timeline_event'));
    }

    protected function case(): ?CaseRecord
    {
        return $this->route('timeline_event')->case;
    }
}
