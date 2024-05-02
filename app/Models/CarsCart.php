<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarsCart extends Model
{
    use HasFactory;

    protected $table = 'cars_cart';

    protected $fillable = [
        'user_id', 'updated_by'
    ];

    /**
     * Get the user associated with the car cart.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who last updated the cart.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the cart items associated with this cart.
     */
    public function items()
    {
        return $this->hasMany(CarCartItem::class, 'cars_cart_id');
    }
}
