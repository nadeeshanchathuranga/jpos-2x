<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAvailableQuantity extends Model
{
    use HasFactory;

    protected $table = 'product_available_quantities';

    protected $fillable = [
        'product_id',
        'batch_number',
        'available_quantity',
        'unit_id',
    ];

    protected $casts = [
        'available_quantity' => 'decimal:2',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(MeasurementUnit::class);
    }
}
