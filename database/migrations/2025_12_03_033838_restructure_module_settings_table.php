<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('module_settings', function (Blueprint $table) {
            // Drop all old columns
            $table->dropColumn([
                'products',
                'purchase_orders',
                'grn',
                'expenses',
                'supplier',
                'product_transfer',
                'pro_notes',
                'customers',
                'discounts',
                'taxes',
                'sales',
                'reports',
                'product_return',
                'users',
                'sms_notification',
                'email_notification',
                'barcode'
            ]);
        });

        Schema::table('module_settings', function (Blueprint $table) {
            // Add new core module columns
            $table->boolean('supplier_purchase')->default(true)->after('id');
            $table->boolean('stock_transfer')->default(true)->after('supplier_purchase');
            $table->boolean('brand_type')->default(true)->after('stock_transfer');
            $table->boolean('tax')->default(true)->after('brand_type');
            $table->boolean('discount')->default(true)->after('tax');
            $table->boolean('sales_return')->default(true)->after('discount');
            
            // Add new optional module columns
            $table->boolean('barcode')->default(false)->after('sales_return');
            $table->boolean('email_notification')->default(false)->after('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('module_settings', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn([
                'supplier_purchase',
                'stock_transfer',
                'brand_type',
                'tax',
                'discount',
                'sales_return',
                'barcode',
                'email_notification'
            ]);
        });

        Schema::table('module_settings', function (Blueprint $table) {
            // Restore old columns
            $table->boolean('products')->default(true);
            $table->boolean('purchase_orders')->default(true);
            $table->boolean('grn')->default(true);
            $table->boolean('expenses')->default(true);
            $table->boolean('supplier')->default(true);
            $table->boolean('product_transfer')->default(true);
            $table->boolean('pro_notes')->default(true);
            $table->boolean('customers')->default(true);
            $table->boolean('discounts')->default(true);
            $table->boolean('taxes')->default(true);
            $table->boolean('sales')->default(true);
            $table->boolean('reports')->default(true);
            $table->boolean('product_return')->default(true);
            $table->boolean('users')->default(true);
            $table->boolean('sms_notification')->default(false);
            $table->boolean('email_notification')->default(false);
            $table->boolean('barcode')->default(false);
        });
    }
};
