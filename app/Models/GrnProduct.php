<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GrnProduct extends Model
{
    use HasFactory;

    protected $table = 'goods_received_notes_products';

    protected $fillable = [
        'goods_received_note_id',
        'product_id',
        'quantity',
        'purchase_price', 
        'discount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function grn()
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'goods_received_note_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
