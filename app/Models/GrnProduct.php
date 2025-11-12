<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnProduct extends Model
{
    
     use HasFactory;

    protected $fillable = [
        'grn_id',
        'product_id',
        'qty',
        'purchase_price',
        'selling_price',
        'discount',
        'total',
    ];

    public function grn()
    {
        return $this->belongsTo(Grn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
