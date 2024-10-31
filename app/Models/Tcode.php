<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tcode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'code', 'deskripsi', 'created_by', 'updated_by'];

    // A tcode can belong to many single roles (many-to-many relationship)
    public function singleRoles()
    {
        return $this->belongsToMany(SingleRole::class, 'single_role_tcode', 'tcode_id', 'single_role_id');
    }

    // A tcode belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}