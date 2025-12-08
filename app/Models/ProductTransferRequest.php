<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTransferRequest extends Model
{
    protected $table = 'product_transfer_requests';

    protected $fillable = [
        'product_transfer_request_no',
        'request_date',
        'remarks',
        'status',
        'user_id',
    ];

    // Relationships
    public function products()
    {
        return $this->hasMany(ProductTransferRequestProduct::class, 'ptr_id');
    }

    public function ptr_products()
    {
        return $this->hasMany(ProductTransferRequestProduct::class, 'ptr_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prns()
    {
        return $this->hasMany(Prn::class, 'ptr_id');
    }
}
