<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceivedNoteReturnProduct extends Model
{
    protected $table = 'goods_received_note_return_products';

    protected $fillable = [
        'goods_received_note_return_id',
        'products_id',
        'qty',
        'remarks',
    ];

    public function grnReturn()
    {
        return $this->belongsTo(GoodsReceivedNoteReturn::class, 'goods_received_note_return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id');
    }
}
