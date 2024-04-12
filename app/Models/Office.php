<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'currency',
        'billing_cycle',
        'size',
        'payment_terms',
        'room_capacity',
        'fee_amount',
        'created_by',
        'updated_by',
    ];

    // Relationship with OfficeRent
    public function officeRents()
    {
        return $this->hasMany(OfficeRent::class);
    }

    // Relationship with User (Creator)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship with User (Updater)
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}