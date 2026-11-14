<?php

namespace App\Models;

use App\Policies\CaseItemPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Pihak terkait (data simulasi). Sengaja TIDAK ada kolom alamat, nomor identitas, atau data pribadi lain.
#[UsePolicy(CaseItemPolicy::class)]
class Suspect extends Model
{
    // Status hukum, terpisah dari status penanganan kasus. Bukan kesimpulan bersalah dari sistem.
    public const LEGAL_STATUSES = [
        'dugaan' => 'Dugaan keterlibatan',
        'tersangka' => 'Tersangka',
        'putusan' => 'Ada putusan pengadilan',
    ];

    protected $fillable = [
        'case_id', 'name', 'legal_status', 'relation_description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }
}
