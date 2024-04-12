<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeRent extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'start_date',
        'end_date',
        'vendor_id',
        'details',
        'created_by',
        'updated_by',
    ];

    // Relationship with Office
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    // Relationship with Vendor
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
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