<?php

namespace App\Http\Requests;

use App\Models\CaseRecord;
use App\Models\Evidence;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Pencarian dan filter daftar kasus (query string). Hak akses diatur scope visibleTo, bukan di sini.
class CaseSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in(CaseRecord::CATEGORIES)],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'status' => ['nullable', Rule::in(array_keys(CaseRecord::STATUSES))],
            'archive_status' => ['nullable', Rule::in([...array_keys(CaseRecord::ARCHIVE_STATUSES), 'semua'])],
            'evidence_type' => ['nullable', Rule::in(array_keys(Evidence::TYPES))],
        ];
    }

    public function attributes(): array
    {
        return [
            'q' => 'kata kunci', 'category' => 'kategori', 'year' => 'tahun', 'status' => 'status penanganan',
            'archive_status' => 'status arsip', 'evidence_type' => 'jenis barang bukti',
        ];
    }
}
