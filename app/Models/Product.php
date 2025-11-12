<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
      use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category',
        'purchase_price',
        'selling_price',
        'discount',
        'qty',
        'return_product',
        'purchase_unit',
        'sales_unit',
        'transfer_unit',
        'conversion_wage',
        'status',
    ];
}
