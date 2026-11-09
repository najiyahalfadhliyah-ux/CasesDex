<?php

namespace App\Http\Requests;

// Mewarisi aturan, pesan, dan nama field dari StoreCaseRequest
class UpdateCaseRequest extends StoreCaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('case'));
    }

    public function rules(): array
    {
        $case = $this->route('case');
        $rules = parent::rules();

        // Petugas tidak boleh memindahkan penugasan atau mengubah tenggat arsip:
        // field-nya dibuang dari aturan sehingga tidak ikut tersimpan
        if ($this->user()->cannot('assign', $case)) {
            unset($rules['assigned_to']);

            return $rules;
        }

        $rules['assigned_to'] = ['nullable', $this->officerRule($case->assigned_to)];
        $rules['archive_deadline'] = ['required', 'date', 'after_or_equal:investigation_start_date'];

        return $rules;
    }
}
