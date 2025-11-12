<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTransferRequestProduct extends Model
{
      use HasFactory;

    protected $fillable = [
        'transfer_request_id',
        'product_id',
        'requested_qty',
        'approved_qty',
        'purchase_price',
        'selling_price',
        'unit_id',
    ];

    public function transferRequest()
    {
        return $this->belongsTo(ProductTransferRequest::class, 'transfer_request_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
