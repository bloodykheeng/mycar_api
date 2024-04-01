<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $table = 'villages';

    protected $fillable = [
        'name',
        'parish_id',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    // Define the relationship with the Parish model
    public function parish()
    {
        return $this->belongsTo(Parish::class, 'parish_id');
    }

    public function populationCensus()
    {
        return $this->hasMany(VillagePopulationCensus::class, 'village_id');
    }
}