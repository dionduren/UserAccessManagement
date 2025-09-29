<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessCheckpoint extends Model
{
    protected $fillable = [
        'company_code',
        'periode_id',
        'step',
        'status',
        'payload',
        'completed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'completed_at' => 'datetime',
    ];

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }
}
