<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseRecord extends Model
{
    use SoftDeletes;

    protected $table = 'cases';

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