<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    use HasFactory;

    protected $table = 'log_audit_trails';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'username',
        'activity_type',
        'model_type',
        'model_id',
        'route',
        'method',
        'status_code',
        'session_id',
        'request_id',
        'ip_address',
        'user_agent',
        'before_data',
        'after_data',
        'logged_at',
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array',
        'logged_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
