<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempUploadSession extends Model
{
    use HasFactory;

    protected $table = 'temp_upload_sessions';

    protected $fillable = [
        'module',
        'periode_id',
        'data'
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
