<?php

namespace App\Models;

use App\Models\CompositeRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompositeAO extends Model
{
    use HasFactory;

    protected $table = 'tr_composite_ao';
    protected $primaryKey = 'id';

    protected $fillable = [
        'composite_role',
        'nama',
        'deskripsi',
        'created_by',
        'updated_by',
    ];

    public function compositeRole()
    {
        return $this->belongsTo(CompositeRole::class, 'composite_role', 'nama');
    }
}
