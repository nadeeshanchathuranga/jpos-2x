<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTransferRequestProduct extends Model
{
    protected $fillable = [
        'ptr_id',
        'product_id',
        'requested_qty',
        'unit_id',
    ];

    // Relationships
    public function ptr()
    {
        return $this->belongsTo(ProductTransferRequest::class, 'ptr_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function measurement_unit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'unit_id');
    }
}
