<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'title',
        'amount',
        'remark',
        'expense_date',
        'payment_type',
        'user_id',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'payment_type' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPaymentTypeNameAttribute()
    {
        $types = [
            0 => 'Cash',
            1 => 'Card',
            2 => 'Cheque',
           
        ];
        return $types[$this->payment_type] ?? 'Unknown';
    }
}
