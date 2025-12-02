<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
   use HasFactory;

    protected $fillable = [
        'invoice_no',
        'customer_id',
        'user_id',
        'total_amount',
        'discount',
        'net_amount',
        'paid_amount',
        'balance',
        'payment_type',
        'sale_date',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    // Relationships
    public function products()
    {
        return $this->hasMany(SalesProduct::class, 'sale_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship: Sale has one Income
    public function income()
    {
        return $this->hasOne(Income::class);
    }
}
