<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tcode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tr_tcodes';
    protected $primaryKey = 'id';
    // protected $primaryKey = 'code';
    // public $incrementing = false;
    // protected $keyType = 'string';

    protected $fillable = [
        // 'company_id',
        'code',
        'sap_module',
        'source',
        'deskripsi',
        'created_by',
        'updated_by'
    ];

    protected $dates = ['deleted_at'];

    // A tcode can belong to many single roles (many-to-many relationship)
    public function singleRoles()
    {
        return $this->belongsToMany(SingleRole::class, 'pt_single_role_tcode', 'tcode_id', 'single_role_id')
            ->withPivot('source', 'created_by', 'updated_by')
            ->withTimestamps();
    }
}
