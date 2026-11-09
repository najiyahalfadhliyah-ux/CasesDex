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

    // Kasus yang boleh dilihat user: admin semua, petugas hanya yang ditugaskan.
    // Dipakai juga nanti untuk dashboard, pencarian, dan statistik.
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->role === 'admin' ? $query : $query->where('assigned_to', $user->id);
    }

    protected $fillable = [
        'case_number', 'title', 'category', 'incident_date', 'recorded_date',
        'investigation_start_date', 'completed_date', 'location', 'description',
        'summary', 'suspected_motive', 'status', 'archive_deadline',
        'assigned_to', 'created_by',
    ];

    protected $casts = [
    'incident_date' => 'date',
    'recorded_date' => 'date',
    'investigation_start_date' => 'date',
    'completed_date' => 'date',
    'archive_deadline' => 'date',
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