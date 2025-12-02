<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnReturnProduct extends Model
{
    protected $table = 'grn_return_products';

    protected $fillable = [
        'grn_return_id',
        'products_id',
        'qty',
        'remarks',
    ];

    public function grnReturn()
    {
        return $this->belongsTo(GrnReturn::class, 'grn_return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id');
    }
}
