<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Por extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'order_date',
        'user_id', 
        'status', 
    ];

    protected $casts = [
        'status' => 'string',
        'order_date' => 'date'
    ];

    /**
     * Get the products for the POR
     */
    public function products()
    {
        return $this->hasMany(PorProduct::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

     public function measurementUnit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'measurement_unit_id');
    }
}
