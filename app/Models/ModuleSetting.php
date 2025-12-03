<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleSetting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'module_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        // Core Modules - Supplier/Purchase group
        'supplier',
        'purchase_order',
        'grn',
        'grn_return',
        
        // Stock Transfer group
        'stock_transfer_request',
        'stock_transfer_receive',
        
        // Brand/Type group
        'brand',
        'type',
        
        // Individual modules
        'tax',
        'discount',
        'sales_return',
        
        // Optional Modules
        'barcode',
        'email_notification',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'supplier' => 'boolean',
        'purchase_order' => 'boolean',
        'grn' => 'boolean',
        'grn_return' => 'boolean',
        'stock_transfer_request' => 'boolean',
        'stock_transfer_receive' => 'boolean',
        'brand' => 'boolean',
        'type' => 'boolean',
        'tax' => 'boolean',
        'discount' => 'boolean',
        'sales_return' => 'boolean',
        'barcode' => 'boolean',
        'email_notification' => 'boolean',
    ];
}
