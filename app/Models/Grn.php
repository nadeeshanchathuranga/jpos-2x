<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grn extends Model
{
    use HasFactory;

    protected $fillable = [
        'por_id',
        'grn_no',
        'supplier_id',
        'user_id',
        'grn_date',
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
