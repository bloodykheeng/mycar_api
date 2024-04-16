<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparePart extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'photo_url',
        'description',
        'price',
        'condition',
        'approval_status',
        'spare_part_type_id',
        'vendor_id',
        'created_by',
        'updated_by',
    ];

    // Define the relationships
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }



    public function sparePartType()
    {
        return $this->belongsTo(SparePartType::class);
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
