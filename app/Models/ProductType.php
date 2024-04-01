<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    use HasFactory;

    // Define the table if it's not the standard naming convention
    protected $table = 'product_types';

    // Specify the fields that are mass assignable
    protected $fillable = [
        'name',
        'description',
        'status',
        'photo_url',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the user that created the product type.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the product type.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Add other relationships here as needed
}