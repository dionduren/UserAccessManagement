<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_login_details';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'company_code',
        'kompartemen_id',
        'department_id',
        'attributes',
        'last_login_at',
        'last_login_ip',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'attributes'    => 'array',
        'last_login_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
