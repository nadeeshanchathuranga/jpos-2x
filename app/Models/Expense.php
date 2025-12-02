<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'amount',
        'remark',
        'expense_date',
        'payment_type',
        'user_id',
        'supplier_id',
        'reference',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'payment_type' => 'integer',
    ];

    // Relationship: Expense belongs to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: Expense belongs to Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
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
