<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'kompartemen_id', 'departemen_id', 'nama_jabatan', 'deskripsi', 'created_by', 'updated_by'];

    // A job role belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // A job role belongs to a department
    public function departemen()
    {
        return $this->belongsTo(Departemen::class);
    }

    // A job role belongs to a compartment
    public function kompartemen()
    {
        return $this->belongsTo(Kompartemen::class);
    }
}
