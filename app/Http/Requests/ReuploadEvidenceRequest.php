<?php

namespace App\Http\Requests;

// Unggah ulang berkas bukti digital. Pesan error diwarisi dari StoreEvidenceRequest.
class ReuploadEvidenceRequest extends StoreEvidenceRequest
{
    public function authorize(): bool
    {
        // Sama seperti cek hash: bukti digital aktif, admin atau petugas kasusnya
        return $this->user()->can('checkHash', $this->route('evidence'));
    }

    public function rules(): array
    {
        $rules = parent::rules()['file'];

        return ['file' => array_values(array_diff($rules, ['exclude_unless:type,digital']))];
    }
}
