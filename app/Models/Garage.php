<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Garage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'photo_url',
        'availability',
        'opening_hours',
        'special_features',
        'created_by',
        'updated_by',
    ];

    // Define the relationships
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reviews()
    {
        return $this->hasMany(GarageReview::class);
    }

}
