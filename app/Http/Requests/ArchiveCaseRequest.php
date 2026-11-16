<?php

namespace App\Http\Requests;

use App\Models\CaseRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Mengarsipkan kasus: khusus admin (CaseRecordPolicy::archive), wajib pilih alasan.
class ArchiveCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('archive', $this->route('case'));
    }

    public function rules(): array
    {
        return ['archive_reason' => ['required', Rule::in(array_keys(CaseRecord::ARCHIVE_REASONS))]];
    }

    public function attributes(): array
    {
        return ['archive_reason' => 'alasan arsip'];
    }
}
