<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditTrail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'log_audit_trails';
    protected $primaryKey = 'id';

    protected $fillable = [
        'activity_type',
        'model_type',
        'model_id',
        'before_data',
        'after_data',
        'user_id',
        'ip_address',
        'user_agent',
        'logged_at',
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array',
    ];
}
