<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_description',
        'photo_url',
        'fee_amount',
        'currency',
        'payment_terms',
        'notes',
        'room_capacity',
        'billing_cycle',
        'created_by',
        'updated_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
