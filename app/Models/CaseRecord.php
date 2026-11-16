<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cases';

    // Nilai kolom status => label tampilan
    public const STATUSES = [
        'dalam_penanganan' => 'Dalam penanganan',
        'selesai' => 'Selesai',
        'ditutup' => 'Ditutup',
        'ditangguhkan' => 'Ditangguhkan',
    ];

    public const CATEGORIES = ['Pencurian', 'Penipuan', 'Kejahatan Siber', 'Perusakan', 'Lainnya'];

    public const ARCHIVE_STATUSES = ['aktif' => 'Aktif', 'diarsipkan' => 'Diarsipkan'];

    public const ARCHIVE_REASONS = [
        'selesai_sebelum_6_bulan' => 'Selesai sebelum enam bulan',
        'mencapai_6_bulan' => 'Mencapai enam bulan',
    ];

    // Kasus yang boleh dilihat user: admin semua, petugas hanya yang ditugaskan.
    // Dipakai juga nanti untuk dashboard, pencarian, dan statistik.
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->role === 'admin' ? $query : $query->where('assigned_to', $user->id);
    }

    // Pencarian + filter daftar kasus. Selalu dipakai SETELAH visibleTo(); semua nilai lewat binding (aman SQL injection).
    // Tanpa filter arsip, hanya kasus aktif yang tampil; 'semua' = aktif + diarsipkan.
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $archive = $filters['archive_status'] ?? 'aktif';

        return $query
            // Dibungkus where(...) agar OR tidak lolos dari batas visibleTo
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($q) => $q
                ->where('case_number', 'like', "%{$term}%")
                ->orWhere('title', 'like', "%{$term}%")
                ->orWhereHas('evidence', fn ($e) => $e->where('name', 'like', "%{$term}%")->orWhere('original_name', 'like', "%{$term}%"))))
            ->when($filters['category'] ?? null, fn ($q, $v) => $q->where('category', $v))
            ->when($filters['year'] ?? null, fn ($q, $v) => $q->whereYear('incident_date', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($archive !== 'semua', fn ($q) => $q->where('archive_status', $archive))
            ->when($filters['evidence_type'] ?? null, fn ($q, $v) => $q->whereHas('evidence', fn ($e) => $e->where('type', $v)));
    }

    protected $fillable = [
        'case_number', 'title', 'category', 'incident_date', 'recorded_date',
        'investigation_start_date', 'completed_date', 'location', 'description',
        'summary', 'suspected_motive', 'status', 'archive_deadline',
        'assigned_to', 'created_by', 'archive_status', 'archived_at', 'archive_reason',
    ];

    protected $casts = [
    'incident_date' => 'date',
    'recorded_date' => 'date',
    'investigation_start_date' => 'date',
    'completed_date' => 'date',
    'archive_deadline' => 'date',
    'archived_at' => 'date',
    'assigned_to' => 'integer', // dibandingkan dengan === di CaseRecordPolicy
    ];

    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class, 'case_id');
    }

    public function suspects(): HasMany
    {
        return $this->hasMany(Suspect::class, 'case_id');
    }

    public function timelineEvents(): HasMany
    {
        return $this->hasMany(TimelineEvent::class, 'case_id')->orderBy('event_time');
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}