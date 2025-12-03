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
        // Drop old columns only if they exist
        Schema::table('module_settings', function (Blueprint $table) {
            $columnsToCheck = [
                'sales',
                'purchase',
                'stock_management',
                'customer',
                'product',
                'category',
                'units',
                'return',
                'invoice',
                'quotation',
                'reports',
                'user_management'
            ];

            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('module_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Add new core module columns without using 'after' for flexibility
        Schema::table('module_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('module_settings', 'products')) {
                $table->boolean('products')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'purchase_orders')) {
                $table->boolean('purchase_orders')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'expenses')) {
                $table->boolean('expenses')->default(true);
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove new columns only if they exist
        Schema::table('module_settings', function (Blueprint $table) {
            $columnsToCheck = [
                'products',
                'purchase_orders',
                'expenses',
                'product_transfer',
                'pro_notes',
                'customers',
                'discounts',
                'taxes',
                'sales',
                'reports',
                'product_return',
                'users'
            ];

            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('module_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Restore old columns only if they don't exist
        Schema::table('module_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('module_settings', 'sales')) {
                $table->boolean('sales')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'purchase')) {
                $table->boolean('purchase')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'stock_management')) {
                $table->boolean('stock_management')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'customer')) {
                $table->boolean('customer')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'product')) {
                $table->boolean('product')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'category')) {
                $table->boolean('category')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'units')) {
                $table->boolean('units')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'return')) {
                $table->boolean('return')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'invoice')) {
                $table->boolean('invoice')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'quotation')) {
                $table->boolean('quotation')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'reports')) {
                $table->boolean('reports')->default(false);
            }
            if (!Schema::hasColumn('module_settings', 'user_management')) {
                $table->boolean('user_management')->default(true);
            }
        });
    }
};
