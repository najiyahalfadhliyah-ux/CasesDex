<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Menambah bukti = mengubah kasus: admin, atau petugas yang ditugaskan
        return $this->user()->can('update', $this->route('case'));
    }

    public function rules(): array
    {
        $max = config('evidence.max_kb');
        $photoTypes = implode(',', config('evidence.photo_types'));
        $fileTypes = implode(',', config('evidence.file_types'));

        return [
            'type' => ['required', Rule::in(['fisik', 'digital'])],
            'recorded_date' => ['required', 'date', 'before_or_equal:today'],
            'name' => ['required_if:type,fisik', 'nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'storage_location' => ['required_if:type,fisik', 'nullable', 'string', 'max:255'],
            // extensions = cek ekstensi nama berkas, mimes = cek tipe asli dari isi berkas
            'photo' => ['nullable', 'file', "extensions:$photoTypes", "mimes:$photoTypes", "max:$max"],
            'file' => ['exclude_unless:type,digital', 'required', 'file', "extensions:$fileTypes", "mimes:$fileTypes", "max:$max"],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi.',
            'max' => ':attribute maksimal :max karakter.',
            'photo.max' => 'Ukuran foto maksimal :max KB.',
            'file.max' => 'Ukuran berkas maksimal :max KB.',
            'file' => ':attribute gagal diunggah. Periksa ukuran berkas.',
            'extensions' => ':attribute harus berformat: :values.',
            'mimes' => 'Isi :attribute tidak sesuai tipe yang diizinkan (:values).',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'in' => ':attribute tidak valid.',
            'recorded_date.before_or_equal' => 'Tanggal pencatatan tidak boleh melewati hari ini.',
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => 'Jenis bukti',
            'recorded_date' => 'Tanggal pencatatan',
            'name' => 'Nama bukti',
            'description' => 'Deskripsi',
            'notes' => 'Keterangan',
            'storage_location' => 'Lokasi penyimpanan',
            'photo' => 'Foto',
            'file' => 'Berkas',
        ];
    }
}
