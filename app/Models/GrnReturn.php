<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnReturn extends Model
{
    protected $table = 'grn_returns';

    protected $fillable = [
        'grn_id',
        'date',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function grn()
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grn_return_products()
    {
        return $this->hasMany(GrnReturnProduct::class, 'grn_return_id');
    }
}
