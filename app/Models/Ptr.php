<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ptr extends Model
{
    protected $fillable = [
        'transfer_no',
        'request_date',
        'remarks',
        'status',
        'user_id',
        'created_by'
    ];

    // Relationships
    public function products()
    {
        return $this->hasMany(PtrProduct::class, 'ptr_id');
    }

    public function ptr_products()
    {
        return $this->hasMany(PtrProduct::class, 'ptr_id');
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
