<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'created_by',
        'updated_by',
    ];

    // Define relationships or additional methods here if needed
    public function counties()
    {
        return $this->hasMany(County::class);
    }
    public function projects()
    {
        return $this->belongsToMany(Department::class, 'project_districts', 'districtId', 'projectId');
    }
}
