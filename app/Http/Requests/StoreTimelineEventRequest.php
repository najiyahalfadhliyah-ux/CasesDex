<?php

namespace App\Http\Requests;

use App\Models\CaseRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTimelineEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->case());
    }

    // Kasus tempat peristiwa berada (UpdateTimelineEventRequest mengambilnya dari peristiwa)
    protected function case(): ?CaseRecord
    {
        return $this->route('case');
    }

    public function rules(): array
    {
        return [
            'event_time' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            // Bukti terkait harus milik kasus yang sama
            'evidence_id' => ['nullable', Rule::exists('evidence', 'id')->where('case_id', $this->case()->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute maksimal :max karakter.',
            'date' => ':attribute harus berupa tanggal dan waktu yang valid.',
            'exists' => ':attribute harus bukti dari kasus ini.',
        ];
    }

    public function attributes(): array
    {
        return [
            'event_time' => 'Tanggal dan waktu kejadian',
            'title' => 'Judul peristiwa',
            'description' => 'Deskripsi rinci',
            'evidence_id' => 'Barang bukti terkait',
        ];
    }
}
