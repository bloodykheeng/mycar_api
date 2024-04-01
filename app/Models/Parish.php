<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parish extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sub_county_id',
        'created_by',
        'updated_by',
    ];

    public function subCounty()
    {
        return $this->belongsTo(SubCounty::class, 'sub_county_id');
    }

    // You can define any additional relationships or methods related to the Parish model here.
    public function villages()
    {
        return $this->hasMany(Village::class, 'parish_id');
    }
}
