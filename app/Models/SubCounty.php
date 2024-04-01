<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCounty extends Model
{
    protected $table = 'sub_counties';

    protected $fillable = [
        'name',
        'county_id',
        'created_by',
        'updated_by',
    ];

    public function county()
    {
        return $this->belongsTo(County::class, 'county_id');
    }
    public function parishes()
    {
        return $this->hasMany(Parish::class, 'sub_county_id');
    }
}
