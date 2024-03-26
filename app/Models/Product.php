<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
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
        'product_brand_id',
        'vendor_id',
        'created_by',
        'updated_by'
    ];

    public function photos()
    {
        return $this->hasMany(ProductPhoto::class, 'product_id');
    }

    public function brand()
    {
        return $this->belongsTo(ProductBrand::class, 'product_brand_id');
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
}
