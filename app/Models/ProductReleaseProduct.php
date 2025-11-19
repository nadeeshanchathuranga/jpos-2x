<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReleaseProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_release_id',
        'product_id',
        'quantity',
        'unit_price',
        'total',
    ];

    /**
     * Relationship: Belongs to a product release
     */
    public function release()
    {
        return $this->belongsTo(ProductRelease::class, 'product_release_id');
    }

    /**
     * Relationship: Belongs to a product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
