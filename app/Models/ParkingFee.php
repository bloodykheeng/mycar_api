<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingFee extends Model
{
    use HasFactory;

    protected $table = 'parking_fees'; // Explicitly define the table if it's not the plural of the class name

    protected $fillable = [
        'name',
        'currency',
        'billing_cycle',
        'status',
        'fee_amount',
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
