<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grn extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_id',
        'grn_no',
        'supplier_id',
        'user_id',
        'grn_date',
        'total_amount',
        'discount',
        'net_amount',
        'remarks',
        'status',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(GrnProduct::class);
    }
}
