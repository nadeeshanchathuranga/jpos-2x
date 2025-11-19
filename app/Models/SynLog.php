<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SynLog extends Model
{
    use HasFactory; 

    protected $fillable = [
        'table_name',
        'record_id',
        'action',
        'synced_at',
        'user_id',
    ];

    // Relation to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
