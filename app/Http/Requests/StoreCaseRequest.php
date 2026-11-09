<?php

namespace App\Http\Requests;

use App\Models\CaseRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class StoreCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CaseRecord::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(CaseRecord::CATEGORIES)],
            'location' => ['required', 'string', 'max:255'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
            'investigation_start_date' => ['required', 'date', 'after_or_equal:incident_date'],
            'description' => ['required', 'string', 'max:5000'],
            'summary' => ['nullable', 'string', 'max:10000'],
            'suspected_motive' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(array_keys(CaseRecord::STATUSES))],
            'completed_date' => ['nullable', 'required_if:status,selesai', 'date', 'after_or_equal:investigation_start_date'],
            'assigned_to' => ['nullable', $this->officerRule()],
        ];
    }

    // Hanya petugas aktif yang bisa ditugaskan (kecuali petugas yang sudah ditugaskan sebelumnya)
    protected function officerRule(?int $currentOfficerId = null): Exists
    {
        return Rule::exists('users', 'id')
            ->where('role', 'petugas')
            ->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $currentOfficerId));
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi jika status Selesai.',
            'max' => ':attribute maksimal :max karakter.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'in' => ':attribute tidak valid.',
            'exists' => ':attribute tidak valid atau sudah nonaktif.',
            'incident_date.before_or_equal' => 'Tanggal kejadian tidak boleh melewati hari ini.',
            'investigation_start_date.after_or_equal' => 'Tanggal mulai investigasi tidak boleh sebelum tanggal kejadian.',
            'completed_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai investigasi.',
            'archive_deadline.after_or_equal' => 'Tenggat arsip tidak boleh sebelum tanggal mulai investigasi.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'Judul',
            'category' => 'Kategori',
            'location' => 'Lokasi kejadian',
            'incident_date' => 'Tanggal kejadian',
            'investigation_start_date' => 'Tanggal mulai investigasi',
            'description' => 'Deskripsi singkat',
            'summary' => 'Ringkasan kasus',
            'suspected_motive' => 'Dugaan motif',
            'status' => 'Status',
            'completed_date' => 'Tanggal selesai',
            'assigned_to' => 'Petugas',
            'archive_deadline' => 'Tenggat arsip',
        ];
    }
}
