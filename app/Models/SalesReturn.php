<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasFactory;

    protected $table = 'sales_return';

    protected $fillable = [
        'sale_id',
        'customer_id',
        'user_id',
        'return_date',
        'status',
    ];

    protected $casts = [
        'return_date' => 'date',
        'status' => 'integer',
    ];

    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Unknown'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
            default => 'gray'
        };
    }

    // Relationships
    public function products()
    {
        return $this->hasMany(SalesReturnProduct::class, 'sales_return_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope for returnable products (where return_product is true)
    public function returnableProducts()
    {
        return $this->products()->whereHas('product', function($query) {
            $query->where('return_product', true);
        });
    }

    // Calculate total refund amount
    public function getTotalRefundAttribute()
    {
        return $this->products->sum('total');
    }
}