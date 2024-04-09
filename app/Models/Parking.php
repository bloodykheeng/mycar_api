<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Parking extends Model
{
    protected $fillable = [
        'car_id',
        'vendor_id',
        'currency',
        'billing_cycle',
        'status',
        'fee_amount',
        'start_date',
        'end_date',
        'details',
        'created_by',
        'updated_by'
    ];

    // Define relationships
    public function car()
    {
        return $this->belongsTo(Product::class, 'car_id');
    }



    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Other model methods...

    protected $appends = ['parking_charge']; // Add the custom attribute to the model's array form

    public function getParkingChargeAttribute()
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = $this->end_date ? Carbon::parse($this->end_date) : Carbon::now();
        $feePerDay = $this->fee_amount;

        if ($this->status === 'active' && !$this->end_date) {
            $endDate = Carbon::now();
        } elseif ($this->end_date) {
            $endDate = Carbon::parse($this->end_date);
        }

        $daysParked = (int) $startDate->diffInDays($endDate);

        // If the start date is in the future, return 0 to prevent negative charges
        if ($startDate->isFuture()) {
            return 0;
        }

        return $daysParked * $feePerDay;
    }
}
