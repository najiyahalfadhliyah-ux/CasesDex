<?php

namespace App\Http\Requests;

use App\Models\AuditLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Filter halaman Riwayat Aktivitas (query string). Akses admin sudah dijaga middleware role:admin.
class AuditLogFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer'],
            'action' => ['nullable', Rule::in(array_keys(AuditLog::ACTIONS))],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    public function attributes(): array
    {
        return ['user_id' => 'pengguna', 'action' => 'jenis aktivitas', 'from' => 'tanggal awal', 'to' => 'tanggal akhir'];
    }
}
