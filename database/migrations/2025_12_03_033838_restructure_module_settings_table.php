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
        // Drop all old columns only if they exist
        Schema::table('module_settings', function (Blueprint $table) {
            $columnsToCheck = [
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
            ];

            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('module_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Add new core module columns
        Schema::table('module_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('module_settings', 'supplier_purchase')) {
                $table->boolean('supplier_purchase')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'stock_transfer')) {
                $table->boolean('stock_transfer')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'brand_type')) {
                $table->boolean('brand_type')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'tax')) {
                $table->boolean('tax')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'discount')) {
                $table->boolean('discount')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'sales_return')) {
                $table->boolean('sales_return')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'barcode')) {
                $table->boolean('barcode')->default(false);
            }
            if (!Schema::hasColumn('module_settings', 'email_notification')) {
                $table->boolean('email_notification')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new columns only if they exist
        Schema::table('module_settings', function (Blueprint $table) {
            $columnsToCheck = [
                'supplier_purchase',
                'stock_transfer',
                'brand_type',
                'tax',
                'discount',
                'sales_return',
                'barcode',
                'email_notification'
            ];

            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('module_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Restore old columns only if they don't exist
        Schema::table('module_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('module_settings', 'products')) {
                $table->boolean('products')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'purchase_orders')) {
                $table->boolean('purchase_orders')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'grn')) {
                $table->boolean('grn')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'expenses')) {
                $table->boolean('expenses')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'supplier')) {
                $table->boolean('supplier')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'product_transfer')) {
                $table->boolean('product_transfer')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'pro_notes')) {
                $table->boolean('pro_notes')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'customers')) {
                $table->boolean('customers')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'discounts')) {
                $table->boolean('discounts')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'taxes')) {
                $table->boolean('taxes')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'sales')) {
                $table->boolean('sales')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'reports')) {
                $table->boolean('reports')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'product_return')) {
                $table->boolean('product_return')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'users')) {
                $table->boolean('users')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'sms_notification')) {
                $table->boolean('sms_notification')->default(false);
            }
            if (!Schema::hasColumn('module_settings', 'email_notification')) {
                $table->boolean('email_notification')->default(false);
            }
            if (!Schema::hasColumn('module_settings', 'barcode')) {
                $table->boolean('barcode')->default(false);
            }
        });
    }
};
