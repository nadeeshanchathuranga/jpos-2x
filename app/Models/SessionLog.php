<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionLog extends Model
{
   use HasFactory; 

    protected $fillable = [
        'user_id',
        'login_time',
        'logout_time',
        'ip_address',
        'device',
    ];

  
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
