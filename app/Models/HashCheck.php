<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HashCheck extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'evidence_id', 'checked_by', 'action', 'file_path', 'original_name', 'size', 'computed_hash',
        'stored_hash', 'is_match', 'checked_at',
    ];

    protected $casts = [
        'is_match' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(Evidence::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}