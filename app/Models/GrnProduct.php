<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GrnProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_id',
        'product_id',
        'qty',
        'purchase_price', 
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
