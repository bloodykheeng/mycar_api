<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarWashFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'fee_amount',
        'product_type_id',
        'created_by',
        'updated_by',
    ];



    public function productType()
    {
        return $this->belongsTo(ProductType::class);
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
