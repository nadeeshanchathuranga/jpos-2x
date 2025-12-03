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
            // Drop old core module columns
            $table->dropColumn([
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
            ]);
        });

        Schema::table('module_settings', function (Blueprint $table) {
            // Add new core module columns
            $table->boolean('products')->default(true)->after('id');
            $table->boolean('purchase_orders')->default(true)->after('products');
            // expenses already exists, keep it as core module
            $table->boolean('product_transfer')->default(true)->after('expenses');
            $table->boolean('pro_notes')->default(true)->after('product_transfer');
            $table->boolean('customers')->default(true)->after('pro_notes');
            $table->boolean('discounts')->default(true)->after('customers');
            $table->boolean('taxes')->default(true)->after('discounts');
            $table->boolean('sales')->default(true)->after('taxes');
            $table->boolean('reports')->default(true)->after('sales');
            $table->boolean('product_return')->default(true)->after('reports');
            $table->boolean('users')->default(true)->after('product_return');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('module_settings', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn([
                'products',
                'purchase_orders',
                'product_transfer',
                'pro_notes',
                'customers',
                'discounts',
                'taxes',
                'sales',
                'reports',
                'product_return',
                'users'
            ]);
        });

        Schema::table('module_settings', function (Blueprint $table) {
            // Restore old columns
            $table->boolean('sales')->default(true);
            $table->boolean('purchase')->default(true);
            $table->boolean('stock_management')->default(true);
            $table->boolean('customer')->default(true);
            $table->boolean('product')->default(true);
            $table->boolean('category')->default(true);
            $table->boolean('units')->default(true);
            $table->boolean('return')->default(true);
            $table->boolean('invoice')->default(true);
            $table->boolean('quotation')->default(true);
            $table->boolean('expenses')->default(false);
            $table->boolean('reports')->default(false);
            $table->boolean('user_management')->default(true);
        });
    }
};
