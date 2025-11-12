<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTransferRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_no',
        'from_location_id',
        'to_location_id',
        'requested_by',
        'approved_by',
        'request_date',
        'approved_date',
        'remarks',
        'status',
    ];

    public function products()
    {
        return $this->hasMany(ProductTransferRequestProduct::class, 'transfer_request_id');
    }

    

    public function requestedUser()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
