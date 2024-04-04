<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'service_type_id',
        'start_date',
        'end_date',
        'service_fee',
        'vendor_id',
        'details',
        'created_by',
        'updated_by',
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
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

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Scope a query to only include inactive services.
     */
    public function scopeInactive($query)
    {
        return $query->where(function ($q) {
            $q->where('end_date', '<', now());
        });
    }

    protected $appends = ['current_status']; // Add the custom attribute to the model's array form

    /**
     * Accessor for the "current_status" attribute.
     */

    public function getCurrentStatusAttribute()
    {
        $now = Carbon::now();
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);

        if ($now->isBetween($startDate, $endDate)) {
            return 'running';
        } elseif ($now->isAfter($endDate)) {
            return 'expired';
        } elseif ($now->isBefore($startDate)) {
            return 'future';
        }

        return 'unknown'; // Fallback status
    }
}