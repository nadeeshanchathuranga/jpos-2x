<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceivedNoteReturn extends Model
{
    protected $table = 'goods_received_note_returns';

    protected $fillable = [
        'goods_received_note_id',
        'date',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function grn()
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'goods_received_note_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grn_return_products()
    {
        return $this->hasMany(GoodsReceivedNoteReturnProduct::class, 'goods_received_note_return_id');
    }
}
