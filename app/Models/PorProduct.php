<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PorProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'por_id',
        'product_id',
        'quantity',
        'measurement_unit_id',
    ];

    
    /**
     * Get the POR that owns the product
     */
    public function por()
    {
        return $this->belongsTo(Por::class);
    }

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function measurementUnit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'measurement_unit_id');
    }
}
