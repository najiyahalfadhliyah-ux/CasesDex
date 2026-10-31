<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}