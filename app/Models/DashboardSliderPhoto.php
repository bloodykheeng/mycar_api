<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DashboardSliderPhoto extends Model
{
    use HasFactory;

    // Define the fillable attributes
    protected $fillable = [
        'title',
        'photo_url',
        'caption',
        'status',
        'created_by',
        'updated_by'
    ];

    /**
     * Get the user who created the photo.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the photo.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
