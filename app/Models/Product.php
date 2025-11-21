<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'barcode',
        'brand_id',
        'category_id',
        'type_id',
        'measurement_unit_id',
        'discount_id',
        'tax_id',
        'qty',
        'low_stock_margin',
        'purchase_price',        
        'wholesale_price',
        'retail_price',
        'return_product',
        'purchase_unit_id',
        'sales_unit_id',
        'transfer_unit_id',
        'purchase_to_transfer_rate',
        'purchase_to_sales_rate',
        'transfer_to_sales_rate',
        'status',
        'image',
    ];

    // protected $casts = [
    //     'qty' => 'integer',
    //     'purchase_price' => 'decimal:2',
    //     'wholesale_price' => 'decimal:2',
    //     'retail_price' => 'decimal:2',
    //     'return_product' => 'boolean',
    //     'purchase_to_transfer_rate' => 'decimal:2',
    //     'purchase_to_sales_rate' => 'decimal:2',
    //     'transfer_to_sales_rate' => 'decimal:2',
    //     'status' => 'integer',
    // ];

    // Relationships
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function purchaseUnit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'purchase_unit_id');
    }

    public function salesUnit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'sales_unit_id');
    }

    public function transferUnit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'transfer_unit_id');
    }

    public function measurementUnit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'measurement_unit_id');
    }

    

    public function measurementUnits()
    {
        return $this->belongsToMany(MeasurementUnit::class, 'product_measurement_unit')
            ->withTimestamps();
    }
}
