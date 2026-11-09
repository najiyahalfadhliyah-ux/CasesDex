<?php

namespace App\Http\Requests;

use App\Models\CaseRecord;
use Illuminate\Validation\Rule;

// Hanya status + tanggal selesai. Pesan dan nama field diwarisi dari StoreCaseRequest.
class UpdateCaseStatusRequest extends StoreCaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('case'));
    }

    public function rules(): array
    {
        $start = $this->route('case')->investigation_start_date->format('Y-m-d');

        return [
            'status' => ['required', Rule::in(array_keys(CaseRecord::STATUSES))],
            'completed_date' => ['nullable', 'required_if:status,selesai', 'date', "after_or_equal:{$start}", 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'completed_date.before_or_equal' => 'Tanggal selesai tidak boleh melewati hari ini.',
        ];
    }
}
