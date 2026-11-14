<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Evidence extends Model
{
    protected $table = 'evidence';

    public const TYPES = ['fisik' => 'Fisik', 'digital' => 'Digital'];

    protected $fillable = [
        'case_id', 'type', 'recorded_date', 'name', 'description', 'notes', 'photo_path',
        'storage_location', 'file_path', 'original_name', 'format',
        'size', 'sha256_hash', 'is_active', 'uploaded_by',
    ];

    protected $casts = [
        'recorded_date' => 'date',
        'size' => 'integer',
        'is_active' => 'boolean',
    ];

    // Ukuran berkas mudah dibaca. Tidak pakai Number::fileSize karena butuh ekstensi PHP intl.
    public function sizeLabel(): string
    {
        return $this->size >= 1048576
            ? number_format($this->size / 1048576, 1, ',', '.').' MB'
            : number_format($this->size / 1024, 1, ',', '.').' KB';
    }

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
        return $this->hasMany(HashCheck::class)->latest('checked_at')->latest('id');
    }

    public function latestReupload(): HasOne
    {
        return $this->hasOne(HashCheck::class)->ofMany(['id' => 'max'], fn ($q) => $q->where('action', 'unggah_ulang'));
    }

    // Berkas yang sedang berlaku: unggahan ulang terakhir, atau berkas asli.
    // Kolom file_path/original_name/sha256_hash di tabel evidence tetap milik unggahan pertama (acuan).
    public function currentFilePath(): ?string
    {
        return $this->latestReupload?->file_path ?? $this->file_path;
    }
}
