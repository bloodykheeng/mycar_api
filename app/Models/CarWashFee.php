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
        'currency',
        'billing_cycle',
        'status',
        'car_type_id',
        'created_by',
        'updated_by',
    ];



    public function carType()
    {
        return $this->belongsTo(CarType::class);
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
