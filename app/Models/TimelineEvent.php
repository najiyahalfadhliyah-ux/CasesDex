<?php

namespace App\Models;

use App\Policies\CaseItemPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UsePolicy(CaseItemPolicy::class)]
class TimelineEvent extends Model
{
    protected $fillable = [
        'case_id', 'event_time', 'title', 'description',
        'evidence_id', 'recorded_by', 'is_active',
    ];

    protected $casts = [
        'event_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(Evidence::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
