<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evidence extends Model
{
    protected $table = 'evidence';

    protected $fillable = [
        'case_id', 'type', 'name', 'description', 'photo_path',
        'storage_location', 'file_path', 'original_name', 'format',
        'size', 'sha256_hash', 'uploaded_by',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function hashChecks(): HasMany
    {
        return $this->hasMany(HashCheck::class);
    }
}