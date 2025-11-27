<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrNoteProduct extends Model
{
    protected $table = 'prn_produts';  

    protected $fillable = [
        'prn_id',
        'product_id',
        'quantity',
        'unit_price', 
        'total',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function prn()
    {
        return $this->belongsTo(PrNote::class, 'prn_id');
    }
}
