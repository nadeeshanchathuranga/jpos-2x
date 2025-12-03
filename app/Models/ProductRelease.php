<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'ptr_id',
        'user_id',
        'to_location_id',
        'release_date',
        'status',
        'note',
    ];

    /**
     * Relationship: One release has many released products
     */
    public function products()
    {
        return $this->hasMany(ProductReleaseProduct::class, 'product_release_id');
    }

    /**
     * Relationship: Belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Belongs to a product transfer request (optional)
     */
    public function transferRequest()
    {
        return $this->belongsTo(ProductTransferRequest::class, 'ptr_id');
    }
}
