<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class County extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'district_id',
        'created_by',
        'updated_by',
    ];


    public function district()
    {
        return $this->belongsTo(District::class);
    }

    // Define relationships or additional methods here if needed
    public function subCounties()
    {
        return $this->hasMany(SubCounty::class, 'county_id');
    }
}
