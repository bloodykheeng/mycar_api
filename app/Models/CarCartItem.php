<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarCartItem extends Model
{
    use HasFactory;

    protected $table = 'car_cart_items';

    protected $fillable = [
        'cars_cart_id', 'car_id', 'selected_quantity', 'price', 'created_by', 'updated_by'
    ];

    /**
     * Get the cart that the item belongs to.
     */
    public function carsCart()
    {
        return $this->belongsTo(CarsCart::class, 'cars_cart_id');
    }

    /**
     * Get the car associated with the cart item.
     */
    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    /**
     * Get the user who created the cart item.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the cart item.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
