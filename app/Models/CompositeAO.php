<?php

namespace App\Models;

use App\Models\CompositeRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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

    protected $dates = ['deleted_at'];

    public function compositeRoles()
    {
        return $this->belongsToMany(CompositeRole::class,  'nama', 'composite_role');
    }
}
