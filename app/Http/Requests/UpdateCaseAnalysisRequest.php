<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Ubah ringkasan kasus dan/atau dugaan motif dari halaman kronologi.
// "sometimes": tiap kartu boleh mengirim satu field saja.
class UpdateCaseAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('case'));
    }

    public function rules(): array
    {
        return [
            'summary' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'suspected_motive' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return ['max' => ':attribute maksimal :max karakter.'];
    }

    public function attributes(): array
    {
        return ['summary' => 'Ringkasan kasus', 'suspected_motive' => 'Dugaan motif'];
    }
}
