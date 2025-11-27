<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrNoteProduct extends Model
{
      protected $table = 'prn_produts';
     protected $fillable = [
        'prn_id',
        'product_id',
        'qty',
        'purchase_price',
        'discount',
        'total',
    ];

    public function prn()
    {
        return $this->belongsTo(Prn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
