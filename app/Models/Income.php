<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
      use HasFactory; 
      
    protected $fillable = [
        'source',
        'amount',
        'income_date',
        'user_id',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
