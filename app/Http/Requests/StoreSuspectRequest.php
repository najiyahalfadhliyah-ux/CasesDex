<?php

namespace App\Http\Requests;

use App\Models\Suspect;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Hanya field ini yang diterima. Tidak ada field alamat/nomor identitas (data sensitif tidak disimpan).
class StoreSuspectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('case'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_status' => ['required', Rule::in(array_keys(Suspect::LEGAL_STATUSES))],
            'relation_description' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute maksimal :max karakter.',
            'in' => ':attribute tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama atau nama samaran',
            'legal_status' => 'Status hukum',
            'relation_description' => 'Keterkaitan dengan kasus',
        ];
    }
}
