<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceivedNoteReturnProduct extends Model
{
    protected $table = 'goods_received_note_return_products';

    protected $fillable = [
        'goods_received_note_return_id',
        'product_id',
        'quantity',
        'remarks',
    ];

    public function goodsReceivedNoteReturn()
    {
        return $this->belongsTo(GoodsReceivedNoteReturn::class, 'goods_received_note_return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
