<?php

namespace App\Models;

use App\Models\Tcode;
use App\Models\Company;
use App\Models\CompositeRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SingleRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tr_single_roles';
    protected $primaryKey = 'id';

    protected $fillable = ['company_id', 'composite_role_id', 'nama', 'deskripsi', 'created_by', 'updated_by'];

    // A single role belongs to a composite role
    public function compositeRole()
    {
        return $this->belongsTo(CompositeRole::class);
    }

    // A single role can have many tcodes (many-to-many relationship)
    public function tcodes()
    {
        return $this->belongsToMany(Tcode::class, 'vw_single_role_tcode', 'single_role_id', 'tcode_id');
    }

    // A single role belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
