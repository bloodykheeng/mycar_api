<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SparePartCartItem extends Model
{
    use HasFactory;

    protected $table = 'spare_part_cart_items';

    protected $fillable = ['spare_parts_cart_id', 'spare_part_id', 'selected_quantity', 'price', 'created_by', 'updated_by'];

    /**
     * Get the spare parts cart that the item belongs to.
     */
    public function sparePartsCart()
    {
        return $this->belongsTo(SparePartsCart::class, 'spare_parts_cart_id');
    }

    /**
     * Get the spare part associated with the cart item.
     */
    public function sparePart()
    {
        return $this->belongsTo(SparePart::class, 'spare_part_id');
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
