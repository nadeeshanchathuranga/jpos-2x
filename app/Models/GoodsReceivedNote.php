<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodsReceivedNote extends Model
{
    use HasFactory;

    protected $table = 'goods_received_notes';

    protected $fillable = [
        'purchase_order_request_id',
        'goods_received_note_no',
        'supplier_id',
        'user_id',
        'goods_received_note_date',
        'discount',
        'tax_total',
        'remarks',
        'status',
    ];

    public function grnProducts()
    {
        return $this->hasMany(GrnProduct::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
