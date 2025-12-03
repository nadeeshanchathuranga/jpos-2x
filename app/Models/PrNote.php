<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrNote extends Model
{
    protected $table = 'prns';
    
    protected $fillable = [
        'ptr_id',
        'user_id',
        'release_date',
        'status',
        'remark'
    ];

  
    public function ptr()
    {
        return $this->belongsTo(Ptr::class, 'ptr_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prn_products()
    {
        return $this->hasMany(PrNoteProduct::class, 'prn_id');
    }
}
