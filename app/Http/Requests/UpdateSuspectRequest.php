<?php

namespace App\Http\Requests;

// Aturan, pesan, dan nama field sama dengan StoreSuspectRequest
class UpdateSuspectRequest extends StoreSuspectRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('suspect'));
    }
}
