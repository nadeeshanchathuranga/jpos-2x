<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderRequestProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'por_id',
        'product_id',
        'quantity',
        'measurement_unit_id',
    ];

    // Relationships
    public function por()
    {
        return $this->belongsTo(PurchaseOrderRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function measurement_unit()
    {
        return $this->belongsTo(MeasurementUnit::class);
    }
}
