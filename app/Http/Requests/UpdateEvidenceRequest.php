<?php

namespace App\Http\Requests;

// Berkas digital tidak bisa diganti (hash awal harus tetap jadi acuan). Jenis bukti juga tetap.
class UpdateEvidenceRequest extends StoreEvidenceRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('evidence'));
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $isFisik = $this->route('evidence')->type === 'fisik';

        return [
            'recorded_date' => $rules['recorded_date'],
            'name' => ['required', 'string', 'max:255'],
            'description' => $rules['description'],
            'notes' => $rules['notes'],
            'storage_location' => $isFisik ? ['required', 'string', 'max:255'] : ['exclude'],
            'photo' => $isFisik ? $rules['photo'] : ['exclude'],
        ];
    }
}
