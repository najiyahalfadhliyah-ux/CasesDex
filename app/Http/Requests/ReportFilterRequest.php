<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Filter periode laporan (berdasarkan tanggal kejadian). Hak akses data diatur CaseStatistics::base().
class ReportFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    public function attributes(): array
    {
        return ['from' => 'tanggal awal', 'to' => 'tanggal akhir'];
    }
}
