<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'make',
        'model',
        'year',
        'mileage',
        'number_plate',
        'price',
        'color',
        'quantity',
        'visibility',
        'condition',
        'status',
        'car_brand_id',
        'car_type_id',
        'vendor_id',
        'created_by',
        'updated_by'
    ];

    public function photos()
    {
        return $this->hasMany(CarPhoto::class, 'car_id');
    }
    public function videos()
    {
        return $this->hasMany(CarVideo::class);
    }

    public function brand()
    {
        return $this->belongsTo(CarBrand::class, 'car_brand_id');
    }

    public function type()
    {
        return $this->belongsTo(CarType::class, 'car_type_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id'); // Define the relationship
    }


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function booted()
    {
        static::creating(function ($car) {
            $car->slug = static::uniqueSlug($car->name);
        });
    }

    public static function uniqueSlug($string)
    {
        $baseSlug = Str::slug($string, '-');
        if (static::where('slug', $baseSlug)->doesntExist()) {
            return $baseSlug;
        }

        $counter = 1;
        // Limiting the counter to prevent infinite loops
        while ($counter < 1000) {
            $slug = "{$baseSlug}-{$counter}";
            if (static::where('slug', $slug)->doesntExist()) {
                return $slug;
            }
            $counter++;
        }

        // Fallback if reached 1000 iterations (should ideally never happen)
        return "{$baseSlug}-" . uniqid();
    }
}
